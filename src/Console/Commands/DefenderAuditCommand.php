<?php
namespace Metalinked\LaravelDefender\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class DefenderAuditCommand extends Command {
    protected $signature = 'defender:audit';
    protected $description = 'Run a local security audit of your Laravel project';

    public function handle() {
        $this->info('🔒 Running Laravel Defender Security Audit...');

        // 1. Exposed .env
        $this->checkEnvExposed();

        // 2. APP_DEBUG=true
        $this->checkAppDebug();

        // 3. Permissive CORS
        $this->checkCors();

        // 4. Insecure cookies
        $this->checkCookies();

        // 5. Vulnerable versions
        $this->checkLaravelVersion();

        $this->info('✅ Audit complete.');
    }

    protected function checkEnvExposed() {
        $url = config('app.url') ?? 'http://localhost';
        $envUrl = rtrim($url, '/') . '/.env';
        $response = null;

        try {
            $response = Http::timeout(3)->get($envUrl);
        } catch (\Exception $e) {}

        if ($response && $response->ok() && str_contains($response->body(), 'APP_KEY=')) {
            $this->error('⚠️  .env file is publicly accessible at ' . $envUrl);
            $this->line('    ➜ Block access to .env in your web server config (Apache/Nginx).');
        } else {
            $this->info('✔️  .env file is not publicly accessible.');
        }
    }

    protected function checkAppDebug() {
        if (config('app.debug')) {
            $this->error('⚠️  APP_DEBUG is enabled!');
            $this->line('    ➜ Set APP_DEBUG=false in your .env for production.');
        } else {
            $this->info('✔️  APP_DEBUG is disabled.');
        }
    }

    protected function checkCors() {
        $cors = config('cors.allowed_origins') ?? [];
        if (in_array('*', $cors)) {
            $this->error('⚠️  CORS is permissive (allowed_origins = "*")!');
            $this->line('    ➜ Restrict CORS origins in config/cors.php for better security.');
        } else {
            $this->info('✔️  CORS configuration is not permissive.');
        }
    }

    protected function checkCookies() {
        $secure = config('session.secure');
        $httpOnly = config('session.http_only', true);

        if (!$secure) {
            $this->error('⚠️  Session cookies are not set as secure!');
            $this->line('    ➜ Set SESSION_SECURE_COOKIE=true in your .env for HTTPS.');
        } else {
            $this->info('✔️  Session cookies are secure.');
        }

        if (!$httpOnly) {
            $this->error('⚠️  Session cookies are not HTTP only!');
            $this->line('    ➜ Set SESSION_HTTP_ONLY=true in your .env.');
        } else {
            $this->info('✔️  Session cookies are HTTP only.');
        }
    }

    protected function checkLaravelVersion() {
        $laravel = app();
        $version = $laravel::VERSION;
        $this->info('ℹ️  Laravel version: ' . $version);

        // TODO: add a check for known vulnerabilities
        // (but this would require querying an external API or maintaining a list)
    }
}
