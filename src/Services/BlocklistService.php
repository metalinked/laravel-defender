<?php

namespace Metalinked\LaravelDefender\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Metalinked\LaravelDefender\Models\BlockedIp;

class BlocklistService {
    private static function cacheKey(string $ip): string {
        return "defender:blocked_ip:{$ip}";
    }

    private static function cacheTtl(): int {
        return (int) config('defender.blocklist.cache_ttl', 300);
    }

    private static function tableExists(): bool {
        return Schema::hasTable((new BlockedIp)->getTable());
    }

    public static function isBlocked(string $ip): bool {
        if (! config('defender.blocklist.enabled', true) || ! self::tableExists()) {
            return false;
        }

        $cacheKey = self::cacheKey($ip);

        return Cache::remember($cacheKey, self::cacheTtl(), function () use ($ip) {
            return BlockedIp::where('ip', $ip)
                ->where(function ($q) {
                    $q->whereNull('blocked_until')
                      ->orWhere('blocked_until', '>', now());
                })
                ->exists();
        });
    }

    public static function block(string $ip, ?string $reason = null, ?\DateTimeInterface $until = null): void {
        BlockedIp::updateOrCreate(
            ['ip' => $ip],
            ['reason' => $reason, 'blocked_until' => $until]
        );

        Cache::put(self::cacheKey($ip), true, self::cacheTtl());
    }

    public static function unblock(string $ip): bool {
        $deleted = BlockedIp::where('ip', $ip)->delete();
        Cache::forget(self::cacheKey($ip));

        return $deleted > 0;
    }

    public static function all(): Collection {
        return BlockedIp::query()
            ->where(function ($q) {
                $q->whereNull('blocked_until')
                  ->orWhere('blocked_until', '>', now());
            })
            ->orderByDesc('created_at')
            ->get();
    }
}
