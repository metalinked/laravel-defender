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
        $this->info(__('defender::defender.audit_running'));

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

        $this->info(__('defender::defender.audit_complete'));
    }

    protected function checkEnvExposed() {
        $url = config('app.url') ?? 'http://localhost';
        $envUrl = rtrim($url, '/') . '/.env';
        $response = null;

        try {
            $response = Http::timeout(3)->get($envUrl);
        } catch (\Exception $e) {}

        if ($response && $response->ok() && str_contains($response->body(), 'APP_KEY=')) {
            $this->error(__('defender::defender.audit_env_exposed', ['url' => $envUrl]));
            $this->line('    ' . __('defender::defender.audit_env_exposed_tip'));
        } else {
            $this->info(__('defender::defender.audit_env_not_exposed'));
        }
    }

    protected function checkAppDebug() {
        if (config('app.debug')) {
            $this->error(__('defender::defender.audit_debug_enabled'));
            $this->line('    ' . __('defender::defender.audit_debug_tip'));
        } else {
            $this->info(__('defender::defender.audit_debug_disabled'));
        }
    }

    protected function checkCors() {
        $cors = config('cors.allowed_origins') ?? [];
        if (in_array('*', $cors)) {
            $this->error(__('defender::defender.audit_cors_permissive'));
            $this->line('    ' . __('defender::defender.audit_cors_tip'));
        } else {
            $this->info(__('defender::defender.audit_cors_ok'));
        }
    }

    protected function checkCookies() {
        $secure = config('session.secure');
        $httpOnly = config('session.http_only', true);

        if (!$secure) {
            $this->error(__('defender::defender.audit_cookies_insecure'));
            $this->line('    ' . __('defender::defender.audit_cookies_secure_tip'));
        } else {
            $this->info(__('defender::defender.audit_cookies_secure'));
        }

        if (!$httpOnly) {
            $this->error(__('defender::defender.audit_cookies_http_only_missing'));
            $this->line('    ' . __('defender::defender.audit_cookies_http_only_tip'));
        } else {
            $this->info(__('defender::defender.audit_cookies_http_only'));
        }
    }

    protected function checkLaravelVersion() {
        $laravel = app();
        $version = $laravel::VERSION;
        $this->info(__('defender::defender.audit_laravel_version', ['version' => $version]));

        // TODO: add a check for known vulnerabilities
        // (but this would require querying an external API or maintaining a list)
    }
}
