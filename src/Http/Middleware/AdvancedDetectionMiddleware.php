<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Services\AlertManager;

class AdvancedDetectionMiddleware {
    /**
     * Suspicious patterns for path traversal and fuzzing detection.
     */
    protected array $suspiciousPatterns = [
        // Path traversal (classic and encoded)
        '../', '..\\', '../../', '..%2f', '..%5c', '%2e%2e%2f', '%2e%2e\\', '%252e%252e%252f', '%c0%ae%c0%ae%c0%af',
        '%2e%2e%5c', '%2e%2e%252f', '..%255c', '%c1%1c', '%c0%af', '%uff0e%uff0e%u2215', '../../../../',
        // Protocols and wrappers
        'file://', 'php://', 'data://', 'input://', 'expect://', 'gopher://', 'dict://', 'ldap://',
        // Fuzzing tools and common payloads
        'sqlmap', 'acunetix', 'nessus', 'nikto', 'w3af', 'nmap', 'fuzz', 'dirbuster', 'burpsuite',
        // Common fuzzing payloads and attack signatures
        '<script', '<?php', '<?', 'etc/passwd', 'boot.ini', 'select%20', 'union%20select', 'sleep(', 'benchmark(',
        'root:x:0:0:', '<!--', 'onerror=', 'onload=', 'alert(', 'document.cookie', 'window.location', 'base64,', 'eval(', 'system(', 'cmd=', 'shell', 'passwd', 'shadow', 'proc/self/environ'
    ];

    public function handle(Request $request, Closure $next) {
        $advConfig = config('defender.advanced_detection', []);
        $ip = $request->ip();
        $isSuspicious = false;
        $reason = null;

        // 1. Path traversal & fuzzing detection
        $allInputs = array_merge(
            [$request->path(), $request->getRequestUri()],
            $request->all(),
            $request->headers->all()
        );
        $flattenedInputs = json_encode($allInputs);

        foreach ($this->suspiciousPatterns as $pattern) {
            if (stripos($flattenedInputs, $pattern) !== false) {
                $isSuspicious = true;
                $reason = "Suspicious pattern detected: '{$pattern}' in request";
                break;
            }
        }

        // 2. Suspicious user-agent detection
        if (!$isSuspicious && !empty($advConfig['suspicious_user_agents'])) {
            $userAgent = strtolower($request->header('User-Agent', ''));
            foreach ($advConfig['suspicious_user_agents'] as $pattern) {
                if (str_contains($userAgent, $pattern)) {
                    $isSuspicious = true;
                    $reason = __('defender::defender.alert_suspicious_user_agent', ['user_agent' => $userAgent]);
                    break;
                }
            }
        }

        // 3. Suspicious routes detection
        if (!$isSuspicious && !empty($advConfig['suspicious_routes'])) {
            $path = '/' . ltrim($request->path(), '/');
            foreach ($advConfig['suspicious_routes'] as $route) {
                if (str_starts_with($path, $route)) {
                    $isSuspicious = true;
                    $reason = __('defender::defender.alert_suspicious_route', ['route' => $path]);
                    break;
                }
            }
        }

        // 4. Common username detection (for login routes)
        if (
            !$isSuspicious &&
            !empty($advConfig['common_usernames']) &&
            $request->is('login') &&
            in_array(strtolower($request->input('username', '')), $advConfig['common_usernames'])
        ) {
            $isSuspicious = true;
            $reason = __('defender::defender.alert_common_username', ['username' => $request->input('username')]);
        }

        // If suspicious, block the request and alert
        if ($isSuspicious) {
            AlertManager::send(
                __('defender::defender.alert_subject'),
                $reason ?? __('defender::defender.access_blocked'),
                [
                    'ip' => $ip,
                    'route' => $request->path(),
                    'is_suspicious' => true,
                    'request' => $request,
                    'reason' => $reason ?? __('defender::defender.access_blocked'),
                ]
            );
            return response($reason ?? __('defender::defender.access_blocked'), 429);
        }

        return $next($request);
    }
}
