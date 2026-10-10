<?php

namespace Metalinked\LaravelDefender\Listeners;

use Illuminate\Support\Facades\Cache;
use Metalinked\LaravelDefender\Defender;
use Metalinked\LaravelDefender\Events\SuspiciousRequestDetected;
use Metalinked\LaravelDefender\Services\BlocklistService;

/**
 * Escalation: an IP with auto_block.after suspicious requests within
 * auto_block.window_minutes goes to the blocklist for auto_block.hours.
 */
class AutoBlockListener {
    public function handle(SuspiciousRequestDetected $event): void {
        $threshold = (int) config('defender.auto_block.after', 0);

        if (
            $threshold <= 0
            || Defender::monitoring()
            || ! config('defender.blocklist.enabled', true)
            || BlocklistService::isBlocked($event->ip)
        ) {
            return;
        }

        $key = "defender:incidents:{$event->ip}";
        Cache::add($key, 0, now()->addMinutes((int) config('defender.auto_block.window_minutes', 60)));
        $count = (int) Cache::increment($key);

        if ($count >= $threshold) {
            BlocklistService::block(
                $event->ip,
                __('defender::defender.auto_block_reason', ['count' => $count]),
                BlocklistService::autoBlockUntil(),
            );

            Cache::forget($key);
        }
    }
}
