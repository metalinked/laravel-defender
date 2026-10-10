<?php

namespace Metalinked\LaravelDefender\Http\Middleware\Concerns;

use Illuminate\Http\Request;

trait ChecksHoneypot {
    protected function honeypotField(Request $request): ?string {
        $prefix = config('defender.honeypot.field_prefix', 'my_full_name_');

        return collect($request->all())
            ->keys()
            ->first(fn ($key) => is_string($key) && str_starts_with($key, $prefix));
    }

    /**
     * Why the submission looks like a bot, or null if it passes.
     */
    protected function honeypotFailure(Request $request, ?string $field): ?string {
        if ($field && $request->filled($field)) {
            return __('defender::defender.honeypot_bot_detected');
        }

        if (! $request->has('valid_from')) {
            return __('defender::defender.honeypot_missing');
        }

        try {
            $timestamp = (int) decrypt($request->input('valid_from'));
        } catch (\Throwable) {
            return __('defender::defender.honeypot_invalid');
        }

        if (now()->timestamp - $timestamp < (int) config('defender.honeypot.minimum_time', 2)) {
            return __('defender::defender.honeypot_too_quick');
        }

        return null;
    }
}
