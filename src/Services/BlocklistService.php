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

        // Cache the row's actual blocked_until (or `true` for a permanent block, `false` for
        // no row at all) rather than a plain boolean, so a temporary block that expires
        // mid-TTL is recognised immediately instead of staying blocked until the cache entry
        // itself expires.
        $blockedUntil = Cache::remember($cacheKey, self::cacheTtl(), function () use ($ip) {
            $row = BlockedIp::where('ip', $ip)->first(['blocked_until']);

            if (! $row) {
                return false;
            }

            return $row->blocked_until ?? true;
        });

        if ($blockedUntil === false || $blockedUntil === true) {
            return $blockedUntil;
        }

        return $blockedUntil > now();
    }

    public static function block(string $ip, ?string $reason = null, ?\DateTimeInterface $until = null): void {
        BlockedIp::updateOrCreate(
            ['ip' => $ip],
            ['reason' => $reason, 'blocked_until' => $until]
        );

        Cache::put(self::cacheKey($ip), $until ?? true, self::cacheTtl());
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
