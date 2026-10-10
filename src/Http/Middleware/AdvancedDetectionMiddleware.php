<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Defender;

class AdvancedDetectionMiddleware {
    /**
     * Case-insensitive substrings matched against the URL, query string and headers
     * (and the body when advanced_detection.scan_body is on), after URL-decoding
     * twice and collapsing whitespace. Keep them specific: anything that can show
     * up in normal text ("shell", "select ") causes false positives.
     */
    public const PATTERNS = [
        // Path traversal (encoded forms are covered by the double decoding)
        '../', '..\\', '%c0%ae', '%c1%1c', '%uff0e',
        // Stream wrappers and remote lookups
        'file://', 'php://', 'phar://', 'zip://', 'data://', 'expect://', 'gopher://', 'dict://', 'ldap://', 'jndi:',
        // Sensitive files
        'etc/passwd', 'etc/shadow', 'proc/self/environ', 'boot.ini', 'win.ini', 'root:x:0:0:',
        // SQL injection
        'union select', 'union all select', 'information_schema', 'sleep(', 'benchmark(', 'waitfor delay',
        "' or '1'='1", "' or 1=1", 'xp_cmdshell',
        // XSS
        '<script', 'javascript:', 'onerror=', 'onload=', 'document.cookie', 'alert(',
        // Code execution
        '<?php', 'eval(', 'base64_decode(', 'shell_exec(', 'passthru(', 'system(',
        // Scanner signatures
        'sqlmap', 'acunetix', 'nessus', 'nikto', 'w3af', 'dirbuster', 'burpsuite',
    ];

    public function handle(Request $request, Closure $next) {
        $config = config('defender.advanced_detection', []);

        if (! ($config['enabled'] ?? true) || Defender::isWhitelisted($request)) {
            return $next($request);
        }

        $reason = $this->detect($request, $config);

        if ($reason && $response = Defender::block($request, $reason)) {
            return $response;
        }

        return $next($request);
    }

    protected function detect(Request $request, array $config): ?string {
        $haystack = $this->haystack($request, (bool) ($config['scan_body'] ?? false));

        foreach ($this->patterns($config) as $pattern) {
            if (str_contains($haystack, $pattern)) {
                return __('defender::defender.alert_suspicious_pattern', ['pattern' => $pattern]);
            }
        }

        $userAgent = strtolower((string) $request->userAgent());
        foreach ($config['suspicious_user_agents'] ?? [] as $agent) {
            if ($agent !== '' && str_contains($userAgent, strtolower($agent))) {
                return __('defender::defender.alert_suspicious_user_agent', ['user_agent' => $userAgent]);
            }
        }

        $path = strtolower('/' . ltrim($request->path(), '/'));
        foreach ($config['suspicious_routes'] ?? [] as $route) {
            if (str_starts_with($path, strtolower($route))) {
                return __('defender::defender.alert_suspicious_route', ['route' => $path]);
            }
        }

        $usernames = array_map('strtolower', $config['common_usernames'] ?? []);
        if ($usernames && $request->isMethod('post') && $request->is('login')) {
            foreach (['username', 'login', 'email'] as $field) {
                $value = $request->input($field);
                if (is_string($value) && in_array(strtolower(trim($value)), $usernames, true)) {
                    return __('defender::defender.alert_common_username', ['username' => $value]);
                }
            }
        }

        return null;
    }

    /** @return list<string> */
    protected function patterns(array $config): array {
        $ignore = array_map('strtolower', $config['ignore_patterns'] ?? []);
        $patterns = array_merge(self::PATTERNS, $config['extra_patterns'] ?? []);

        return array_values(array_filter(
            array_map('strtolower', $patterns),
            fn ($pattern) => $pattern !== '' && ! in_array($pattern, $ignore, true),
        ));
    }

    /**
     * Everything we inspect, as one normalised lowercase string. Each value is kept
     * both raw (for overlong/encoded signatures) and decoded twice (for %2e%2e%2f
     * and double-encoded %252e...).
     */
    protected function haystack(Request $request, bool $scanBody): string {
        $values = [$request->getRequestUri()];
        $this->flatten($request->query(), $values);
        $this->flatten($scanBody ? $request->all() : [], $values);

        foreach ($request->headers->all() as $name => $headerValues) {
            if ($name !== 'cookie') {
                $this->flatten($headerValues, $values);
            }
        }

        $parts = [];
        foreach ($values as $value) {
            $decoded = urldecode(urldecode($value));
            $parts[] = $value;
            $parts[] = $decoded === $value ? '' : $decoded;
        }

        $haystack = strtolower(implode("\n", $parts));
        // SQL comments are a common way to dodge whitespace checks: UNION/**/SELECT
        $haystack = str_replace('/**/', ' ', $haystack);

        return preg_replace('/[ \t\r\x0B\f]+/', ' ', $haystack) ?? $haystack;
    }

    /** @param list<string> $out */
    protected function flatten(mixed $input, array &$out): void {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                if (is_string($key)) {
                    $out[] = $key;
                }
                $this->flatten($value, $out);
            }
        } elseif (is_scalar($input)) {
            $out[] = (string) $input;
        }
    }
}
