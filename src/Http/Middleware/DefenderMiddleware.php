<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

/**
 * The whole request-level stack in the right order, as one middleware:
 * blocklist → attack detection → country access.
 * Register it globally so probes to routes that don't exist (/wp-admin, /.env) are caught too.
 */
class DefenderMiddleware {
    public const STACK = [
        BlockedIpMiddleware::class,
        AdvancedDetectionMiddleware::class,
        CountryAccessMiddleware::class,
    ];

    public function handle(Request $request, Closure $next) {
        return (new Pipeline(app()))
            ->send($request)
            ->through(self::STACK)
            ->then($next);
    }
}
