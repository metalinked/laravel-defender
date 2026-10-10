<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Defender;
use Metalinked\LaravelDefender\Http\Middleware\Concerns\ChecksHoneypot;

/**
 * Added to the 'web' group: validates any POST that carries the honeypot
 * field, and leaves every other request alone.
 */
class HoneypotAutoMiddleware {
    use ChecksHoneypot;

    public function handle(Request $request, Closure $next) {
        if (! $request->isMethod('post') || Defender::isWhitelisted($request)) {
            return $next($request);
        }

        $field = $this->honeypotField($request);
        $reason = $field ? $this->honeypotFailure($request, $field) : null;

        if ($reason && $response = Defender::block($request, $reason, 422)) {
            return $response;
        }

        return $next($request);
    }
}
