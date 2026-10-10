<?php

namespace Metalinked\LaravelDefender;

use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Events\IpBlocked;
use Metalinked\LaravelDefender\Services\AlertManager;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shared decisions for every protection: is this request trusted, are we
 * only monitoring, and what does a blocked request get back.
 */
class Defender {
    public static function monitoring(): bool {
        return config('defender.mode', 'block') === 'monitor';
    }

    public static function isWhitelisted(Request $request): bool {
        $ips = (array) config('defender.whitelist.ips', []);
        if ($ips && IpUtils::checkIp((string) $request->ip(), $ips)) {
            return true;
        }

        $paths = (array) config('defender.whitelist.paths', []);

        return $paths && $request->is(...$paths);
    }

    /**
     * Record a threat and, unless in monitor mode, return the response that blocks it.
     * Returns null when the request should go through (monitor mode).
     */
    public static function block(Request $request, string $reason, int $status = 403): ?Response {
        AlertManager::send(__('defender::defender.alert_subject'), $reason, [
            'ip' => $request->ip(),
            'route' => $request->path(),
            'is_suspicious' => true,
            'request' => $request,
            'reason' => $reason,
        ]);

        if (self::monitoring()) {
            return null;
        }

        event(new IpBlocked((string) $request->ip(), $reason, $request));

        return self::response($request, $status);
    }

    /**
     * The response a blocked client sees. Deliberately generic: telling an attacker
     * which rule matched only helps them get around it. Publish the defender-views
     * tag to customise the HTML page.
     */
    public static function response(Request $request, int $status): Response {
        $message = $status === 429
            ? __('defender::defender.too_many_attempts')
            : __('defender::defender.access_blocked');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return response()->view('defender::blocked', ['message' => $message, 'status' => $status], $status);
    }
}
