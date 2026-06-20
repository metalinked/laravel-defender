<?php

namespace Metalinked\LaravelDefender\Listeners;

use Illuminate\Support\Facades\Cache;
use Metalinked\LaravelDefender\Events\IpBlocked;
use Metalinked\LaravelDefender\Services\BlocklistService;

class AutoBlockListener {
    public function handle(IpBlocked $event): void {
        $threshold = (int) config('defender.blocklist.auto_block_after', 0);

        if (! $threshold || ! config('defender.blocklist.enabled', true)) {
            return;
        }

        if (BlocklistService::isBlocked($event->ip)) {
            return;
        }

        $cacheKey = "defender:auto_block_count:{$event->ip}";
        $windowHours = (int) config('defender.blocklist.auto_block_window_hours', 24);

        $count = Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $count, now()->addHours($windowHours));

        if ($count >= $threshold) {
            $hours = config('defender.blocklist.auto_block_hours');
            $until = $hours ? now()->addHours((int) $hours) : null;

            BlocklistService::block(
                $event->ip,
                __('defender::defender.auto_block_reason', ['count' => $count]),
                $until
            );

            Cache::forget($cacheKey);
        }
    }
}
