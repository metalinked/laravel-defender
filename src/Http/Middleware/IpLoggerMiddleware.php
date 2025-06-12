<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Models\IpLog;
use Metalinked\LaravelDefender\Detection\GeoService;

class IpLoggerMiddleware {
    
    public function handle(Request $request, Closure $next) {
        // Get additional request data for logging
        $userAgent = $request->userAgent();
        $referer = $request->headers->get('referer');
        $countryCode = GeoService::getCountryCode($request->ip());
        $headersHash = hash('sha256', json_encode($request->headers->all()));

        IpLog::create([
            'ip' => $request->ip(),
            'route' => $request->path(),
            'method' => $request->method(),
            'user_id' => auth()->check() ? auth()->id() : null,
            'is_suspicious' => false,
            'reason' => null,
            'user_agent' => $userAgent,
            'referer' => $referer,
            'country_code' => $countryCode,
            'headers_hash' => $headersHash,
        ]);

        return $next($request);
    }
}
