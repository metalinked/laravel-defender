<?php

namespace Metalinked\LaravelDefender\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DefenderAuditCommand extends Command {
    protected $signature = 'defender:audit';
    protected $description = 'Run a local security audit of your Laravel project';

    public function handle(): void {
        $this->info(__('defender::defender.audit_running'));

        $this->checkEnvExposed();
        $this->checkAppDebug();
        $this->checkCors();
        $this->checkCookies();
        $this->checkAppKey();
        $this->checkSecurityHeaders();
        $this->checkLaravelVersion();

        $this->info(__('defender::defender.audit_complete'));
    }

    protected function checkEnvExposed(): void {
        $url = config('app.url') ?? 'http://localhost';
        $envUrl = rtrim($url, '/') . '/.env';
        $response = null;

        try {
            $response = Http::timeout(3)->get($envUrl);
        } catch (\Exception $e) {
        }

        if ($response && $response->ok() && str_contains($response->body(), 'APP_KEY=')) {
            $this->error(__('defender::defender.audit_env_exposed', ['url' => $envUrl]));
            $this->line('    ' . __('defender::defender.audit_env_exposed_tip'));
        } else {
            $this->info(__('defender::defender.audit_env_not_exposed'));
        }
    }

    protected function checkAppDebug(): void {
        if (config('app.debug')) {
            $this->error(__('defender::defender.audit_debug_enabled'));
            $this->line('    ' . __('defender::defender.audit_debug_tip'));
        } else {
            $this->info(__('defender::defender.audit_debug_disabled'));
        }
    }

    protected function checkCors(): void {
        $cors = config('cors.allowed_origins') ?? [];
        if (in_array('*', $cors)) {
            $this->error(__('defender::defender.audit_cors_permissive'));
            $this->line('    ' . __('defender::defender.audit_cors_tip'));
        } else {
            $this->info(__('defender::defender.audit_cors_ok'));
        }
    }

    protected function checkCookies(): void {
        if (! config('session.secure')) {
            $this->error(__('defender::defender.audit_cookies_insecure'));
            $this->line('    ' . __('defender::defender.audit_cookies_secure_tip'));
        } else {
            $this->info(__('defender::defender.audit_cookies_secure'));
        }

        if (! config('session.http_only', true)) {
            $this->error(__('defender::defender.audit_cookies_http_only_missing'));
            $this->line('    ' . __('defender::defender.audit_cookies_http_only_tip'));
        } else {
            $this->info(__('defender::defender.audit_cookies_http_only'));
        }
    }

    protected function checkAppKey(): void {
        $appKey = config('app.key');
        if (! $appKey || strlen($appKey) < 32 || $appKey === 'SomeRandomString') {
            $this->error(__('defender::defender.audit_app_key_insecure'));
            $this->line('    ' . __('defender::defender.audit_app_key_tip'));
        } else {
            $this->info(__('defender::defender.audit_app_key_secure'));
        }
    }

    protected function checkSecurityHeaders(): void {
        $url = config('app.url') ?? 'http://localhost';
        $this->line(__('defender::defender.audit_headers_checking'));

        try {
            $response = Http::timeout(3)->get($url);

            $recommended = [
                'X-Frame-Options'        => 'audit_headers_x_frame_options_tip',
                'X-Content-Type-Options' => 'audit_headers_x_content_type_tip',
                'Referrer-Policy'        => 'audit_headers_referrer_policy_tip',
                'Strict-Transport-Security' => 'audit_headers_hsts_tip',
            ];

            $missing = [];
            foreach ($recommended as $header => $tipKey) {
                if (! $response->header($header)) {
                    $missing[$header] = $tipKey;
                }
            }

            if ($missing) {
                foreach ($missing as $header => $tipKey) {
                    $this->error(__('defender::defender.audit_headers_missing', ['header' => $header]));
                    $this->line('    ' . __("defender::defender.{$tipKey}"));
                }
            } else {
                $this->info(__('defender::defender.audit_headers_ok'));
            }
        } catch (\Exception $e) {
            $this->line(__('defender::defender.audit_headers_unreachable'));
        }
    }

    protected function checkLaravelVersion(): void {
        $version = app()::VERSION;
        $this->info(__('defender::defender.audit_laravel_version', ['version' => $version]));
    }
}
