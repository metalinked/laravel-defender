# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

**Laravel Defender** is a Composer package (not a full Laravel app) that provides modular security middleware for Laravel 10/11 applications. Namespace: `Metalinked\LaravelDefender`. The entry point is `DefenderServiceProvider`, which auto-discovers via the `extra.laravel` key in `composer.json`.

## Commands

```bash
# Run all tests
composer test
# or
./vendor/bin/phpunit --no-coverage

# Run a single test file
./vendor/bin/phpunit tests/BruteForceMiddlewareTest.php

# Static analysis
composer analyse          # phpstan
composer format           # php-cs-fixer (auto-fix)
composer format-dry       # dry-run (no changes)

# Run everything (format + analyse + test)
composer quality
```

Tests require PHP extensions `sqlite3` and `pdo_sqlite` (Testbench uses SQLite in-memory).

## Architecture

### Core flow

All detection/blocking goes through middleware → `AlertManager::send()` → configured channels. Events are fired for extensibility.

```
Request
  → BlockedIpMiddleware (DB blocklist check, cache-first)
  → AdvancedDetectionMiddleware (patterns, user-agents, routes)
  → BruteForceMiddleware (too many suspicious requests from same IP)
  → CountryAccessMiddleware (geo-blocking)
       ↓ on threat detected
  AlertManager::send() → log / database / mail / slack / webhook
  event(SuspiciousRequestDetected)
  event(IpBlocked)          ← fired only when returning HTTP 429 or 403
       ↓
  Returns HTTP 429 (detected threat) or 403 (blocklisted IP)
```

### Key classes

| File | Role |
|------|------|
| `src/DefenderServiceProvider.php` | Registers middlewares, commands, views, Blade directive, Pulse card |
| `src/Services/AlertManager.php` | Alert dispatcher — routes to all configured channels, writes to DB, fires `SuspiciousRequestDetected` |
| `src/Services/BlocklistService.php` | Dynamic IP blocklist — DB-backed with configurable cache TTL |
| `src/Events/SuspiciousRequestDetected.php` | Fired by `AlertManager::send()` whenever `is_suspicious = true` |
| `src/Events/IpBlocked.php` | Fired by each blocking middleware before returning 429/403 |
| `src/Models/IpLog.php` | Eloquent model for `defender_ip_logs` |
| `src/Models/BlockedIp.php` | Eloquent model for `defender_blocked_ips` |
| `src/Detection/GeoService.php` | Country-code lookup via ip-api / ipinfo / ipgeolocation, with cache |
| `src/Pulse/DefenderPulseCard.php` | Optional Laravel Pulse card (only registered if Pulse + Livewire installed) |

### Middleware stack

| Alias | Class | What it does |
|-------|-------|--------------|
| `defender.blocked` | `BlockedIpMiddleware` | Checks dynamic blocklist (cache-first, then DB). Returns 403. |
| `defender.honeypot` | `HoneypotMiddleware` | Manual honeypot check for a specific route |
| `defender.iplogger` / `ip.logger` | `IpLoggerMiddleware` | Writes to Laravel log (not DB) when `ip_logging.log_all = true` |
| `advanced.detection` | `AdvancedDetectionMiddleware` | Detects suspicious user-agents, routes, common usernames, path traversal, fuzzing |
| `brute.force` | `BruteForceMiddleware` | Counts recent **suspicious** IpLog entries per IP (30s cache layer on DB query) |
| `country.access` | `CountryAccessMiddleware` | Allow/deny by country via `GeoService` |
| *(auto, web group)* | `HoneypotAutoMiddleware` | Applied globally to POST requests when `honeypot.auto_protect_forms = true` |

**Important differences between honeypot middlewares:** `HoneypotMiddleware` aborts if `valid_from` is missing. `HoneypotAutoMiddleware` silently passes through if no honeypot field is present in the request — it only validates when the `@defenderHoneypot` Blade field is actually submitted.

**BruteForce behavior:** Queries `IpLog` counting only `is_suspicious = true` rows within the decay window. A 30-second cache wrapper reduces DB load. Requires `database` alert channel + migration to function. When blocking, calls `AlertManager::send()` and fires `IpBlocked`.

**IpLoggerMiddleware:** When `log_all = true`, writes to Laravel's log file (not to the DB). The `database` channel is only written to by `AlertManager::send()` when a threat is detected.

### Events (extensibility)

Users can listen to these events in their app:

```php
// In EventServiceProvider or AppServiceProvider
Event::listen(SuspiciousRequestDetected::class, function ($event) {
    // $event->request, $event->ip, $event->reason
});

Event::listen(IpBlocked::class, function ($event) {
    // $event->ip, $event->reason, $event->request
});
```

`SuspiciousRequestDetected` fires every time `AlertManager::send()` is called with `is_suspicious = true`. `IpBlocked` fires only when a middleware actually returns a blocking response (429/403).

### Dynamic blocklist

```bash
php artisan defender:block-ip 1.2.3.4 --reason="Brute force" --hours=24
php artisan defender:block-ip 1.2.3.4 --reason="Persistent attacker"  # permanent
php artisan defender:unblock-ip 1.2.3.4
php artisan defender:block-list
```

Uses `defender_blocked_ips` table (requires `defender-migrations` to be published and run). Cache TTL is configurable via `defender.blocklist.cache_ttl` (default 300s). `BlocklistService::isBlocked()` checks cache first, then DB. Add `defender.blocked` middleware globally to enforce it.

### Pulse card (optional)

Requires `laravel/pulse` and `livewire/livewire` installed. Auto-registered when both are detected. Add to the Pulse dashboard:

```blade
<livewire:defender-pulse-card cols="4" />
```

Shows: threats in last hour, total threats, top attacking IPs, recent detections. Polls every 10 seconds.

### Configuration

Published as `config/defender.php`. All config lives under the `defender.*` key. Sections: `honeypot`, `ip_logging`, `brute_force`, `advanced_detection` (includes `country_access` and `geo_provider`), `blocklist`, `alerts`.

### Translations

`resources/lang/{en,es,ca}/defender.php` — all user-facing strings use `__('defender::defender.*')` keys. All three files must stay in sync when adding new keys.

### Artisan commands

| Command | Purpose |
|---------|---------|
| `defender:ip-logs` | View DB logs (`--suspicious`, `--ip`, `--limit`) |
| `defender:export-logs` | Export to CSV/JSON (`--format`, `--ip`, `--from`, `--to`, `--output`) |
| `defender:prune-logs` | Delete old logs (`--days`, `--laravel`) |
| `defender:stats` | Summary stats from `defender_ip_logs` |
| `defender:audit` | Security audit: env, debug, CORS, cookies, APP_KEY, security headers |
| `defender:block-ip` | Block an IP (`--reason`, `--hours`) |
| `defender:unblock-ip` | Remove an IP from the blocklist |
| `defender:block-list` | List all currently blocked IPs |

### Testing setup

Tests extend `Metalinked\LaravelDefender\Tests\TestCase`, which uses Orchestra Testbench with SQLite in-memory and `RefreshDatabase`. The base `TestCase` loads migrations from `database/migrations/` and sets `defender.alerts.channels = ['database']` by default. Individual test files may override config in `getEnvironmentSetUp()`.

### Migrations

Two migration files, both under `defender-migrations` publish tag:
- `2025_06_01_000001_create_defender_ip_logs_table.php` — access/alert log
- `2025_06_01_000002_create_defender_blocked_ips_table.php` — dynamic blocklist
