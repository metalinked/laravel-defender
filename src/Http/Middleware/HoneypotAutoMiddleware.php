<?php
namespace LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HoneypotAutoMiddleware
{
    public function handle(Request $request, Closure $next) {
        // Només per POST
        if ($request->isMethod('post')) {
            $prefix = config('defender.honeypot.field_prefix', 'my_full_name_');
            $honeypotField = collect($request->all())
                ->filter(fn($value, $key) => str_starts_with($key, $prefix))
                ->keys()
                ->first();

            if ($honeypotField) {
                if ($request->filled($honeypotField)) {
                    abort(422, 'Bot detected');
                }

                if ($request->has('valid_from')) {
                    try {
                        $timestamp = decrypt($request->input('valid_from'));
                        $minTime = config('defender.honeypot.minimum_time', 2);
                        if (now()->timestamp - $timestamp < $minTime) {
                            abort(422, 'Form submitted too quickly');
                        }
                    } catch (\Exception $e) {
                        abort(422, 'Invalid honeypot');
                    }
                } else {
                    abort(422, 'Missing honeypot');
                }
            }
        }

        return $next($request);
    }
}
