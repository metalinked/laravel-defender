<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Defender;
use Metalinked\LaravelDefender\Http\Middleware\Concerns\ChecksHoneypot;

/**
 * Per-route honeypot: the form must include @defenderHoneypot.
 */
class HoneypotMiddleware {
    use ChecksHoneypot;

    public function handle(Request $request, Closure $next) {
        if (Defender::isWhitelisted($request)) {
            return $next($request);
        }

        $reason = $this->honeypotFailure($request, $this->honeypotField($request));

        if ($reason && $response = Defender::block($request, $reason, 422)) {
            return $response;
        }

        return $next($request);
    }
}
