# Laravel Defender

[![Tests](https://img.shields.io/github/actions/workflow/status/metalinked/laravel-defender/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/metalinked/laravel-defender/actions/workflows/tests.yml)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/metalinked/laravel-defender/phpstan.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/metalinked/laravel-defender/actions/workflows/phpstan.yml)
[![GitHub Release](https://img.shields.io/github/v/release/metalinked/laravel-defender?style=flat-square)](https://github.com/metalinked/laravel-defender/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/metalinked/laravel-defender?style=flat-square)](https://packagist.org/packages/metalinked/laravel-defender)
[![License](https://img.shields.io/packagist/l/metalinked/laravel-defender?style=flat-square)](https://github.com/metalinked/laravel-defender/blob/main/LICENSE.md)

A modular security package for Laravel with advanced threat detection, brute force protection, dynamic IP blocklisting with auto-block, country access control, honeypot spam protection, multi-channel alerts, and a Laravel Pulse dashboard card. Fully configurable and privacy-friendly.

---

## Requirements

| Laravel | PHP   |
|---------|-------|
| 11.x    | ^8.2  |
| 12.x    | ^8.2  |
| 13.x    | ^8.2  |

---

## Features

- 🛡️ **Honeypot spam protection** for forms
- 👁️ **Request logging** and alert system for suspicious activity
- 🚨 **Advanced risk detection**: malicious user-agents, common attack routes, login attempts with common usernames, path traversal, and fuzzing patterns
- 🔒 **Brute force protection**: blocks IPs after too many suspicious requests
- 🌍 **Country access control**: allow or deny by country code, with IP whitelist bypass
- 🚫 **Dynamic IP blocklist**: block/unblock IPs at runtime via Artisan, no config changes needed
- 🤖 **Auto-block**: automatically block IPs that trigger repeated events within a configurable time window
- 🎯 **Laravel Events**: `SuspiciousRequestDetected` and `IpBlocked` for full extensibility
- 🔔 **Multi-channel alerts**: log, database, mail, Slack, webhook
- 📊 **Laravel Pulse card**: real-time security dashboard (optional, requires `laravel/pulse`)
- 🔍 **Security audit command**: detects common Laravel misconfigurations and missing security headers
- 📝 **Artisan commands**: view, export, and prune logs directly from the console

---

## Installation

**1. Install via Composer:**

```bash
composer require metalinked/laravel-defender
```

**2. Publish the config file:**

```bash
php artisan vendor:publish --tag=defender-config
```

**3. Publish and run the migrations:**

```bash
php artisan vendor:publish --tag=defender-migrations
php artisan migrate
```

This creates two tables:
- `defender_ip_logs`: stores access logs and security alerts
- `defender_blocked_ips`: stores dynamically blocked IPs

> The `database` alert channel and the IP blocklist both require the migrations to be run. If you only use `log`-based alerts and no blocklist, you can skip the migrations.

---

## Global Protection (Recommended)

Register Defender's middlewares globally to protect all requests, including those to non-existent routes like `/wp-admin`, `/phpmyadmin`, and `/xmlrpc.php`.

In `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\Metalinked\LaravelDefender\Http\Middleware\BlockedIpMiddleware::class);
    $middleware->append(\Metalinked\LaravelDefender\Http\Middleware\AdvancedDetectionMiddleware::class);
    $middleware->append(\Metalinked\LaravelDefender\Http\Middleware\BruteForceMiddleware::class);
    $middleware->append(\Metalinked\LaravelDefender\Http\Middleware\CountryAccessMiddleware::class);
})
```

| Middleware | Description |
|---|---|
| `BlockedIpMiddleware` | Instantly blocks IPs in the dynamic blocklist (returns 403). Run this first. |
| `AdvancedDetectionMiddleware` | Detects malicious user-agents, attack routes, and common usernames. |
| `BruteForceMiddleware` | Blocks IPs after too many suspicious requests in the configured window. |
| `CountryAccessMiddleware` | Allows or denies access based on country code. |

---

## Configuration

All options are in `config/defender.php` after publishing.

### IP Logging & Brute Force

```php
'ip_logging' => [
    'enabled' => true,
    'log_all'  => false, // Log every request, not just suspicious ones. Only for debugging.
],

'brute_force' => [
    'max_attempts'  => 5,
    'decay_minutes' => 10,
],
```

### Advanced Detection & Country Access

```php
'advanced_detection' => [
    'enabled'                => true,
    'geo_provider'           => 'ip-api',       // 'ip-api', 'ipinfo', 'ipgeolocation'
    'geo_cache_minutes'      => 10,
    'ipinfo_token'           => env('IPINFO_TOKEN'),
    'ipgeolocation_key'      => env('IPGEOLOCATION_KEY'),
    'suspicious_user_agents' => ['curl', 'python', 'sqlmap', 'nmap', 'nikto', 'fuzzer', 'scanner'],
    'suspicious_routes'      => ['/wp-admin', '/wp-login', '/phpmyadmin', '/admin.php', '/xmlrpc.php'],
    'common_usernames'       => ['admin', 'administrator', 'root', 'test', 'user'],
    'country_access' => [
        'mode'          => 'allow',       // 'allow' = only listed countries; 'deny' = block listed countries
        'countries'     => ['ES'],
        'whitelist_ips' => ['1.2.3.4'],  // Always allowed, regardless of country
    ],
],
```

**Geo providers:**
- [ip-api.com](https://ip-api.com/): free tier, no registration required (default)
- [ipinfo.io](https://ipinfo.io/): requires `IPINFO_TOKEN`
- [ipgeolocation.io](https://ipgeolocation.io/): requires `IPGEOLOCATION_KEY`

### Dynamic IP Blocklist & Auto-block

```php
'blocklist' => [
    'enabled'                 => true,
    'cache_ttl'               => 300,   // Seconds to cache each IP's blocked status
    'auto_block_after'        => null,  // IpBlocked events before auto-blocking (null = disabled)
    'auto_block_hours'        => null,  // Duration of auto-block in hours (null = permanent)
    'auto_block_window_hours' => 24,    // Sliding window in hours for counting events
],
```

### Alerts

```php
'alerts' => [
    'channels' => ['log', 'database'], // Also: 'mail', 'slack', 'webhook'
    'mail'     => ['to' => env('DEFENDER_ALERT_MAIL_TO')],
    'slack'    => ['webhook_url' => env('DEFENDER_SLACK_WEBHOOK')],
    'webhook'  => ['url' => env('DEFENDER_ALERT_WEBHOOK')],
],
```

### Environment Variables

| Variable | Description |
|---|---|
| `DEFENDER_GEO_PROVIDER` | Geo provider: `ip-api`, `ipinfo`, or `ipgeolocation` |
| `IPINFO_TOKEN` | API token for ipinfo.io |
| `IPGEOLOCATION_KEY` | API key for ipgeolocation.io |
| `DEFENDER_ALERT_MAIL_TO` | Email address for alert notifications |
| `DEFENDER_SLACK_WEBHOOK` | Slack incoming webhook URL |
| `DEFENDER_ALERT_WEBHOOK` | Custom webhook URL for alert notifications |

---

## Dynamic IP Blocklist

Block and unblock IPs at runtime without touching config files or deploying:

```bash
# Block permanently
php artisan defender:block-ip 1.2.3.4 --reason="Persistent attacker"

# Block for 24 hours
php artisan defender:block-ip 1.2.3.4 --reason="Brute force" --hours=24

# Unblock
php artisan defender:unblock-ip 1.2.3.4

# List all currently blocked IPs
php artisan defender:block-list
```

Blocked IPs are stored in `defender_blocked_ips` and cached (default 5 minutes per IP) for fast lookup on every request.

---

## Auto-block

Defender can automatically block IPs that trigger repeated `IpBlocked` events within a time window, useful for catching persistent attackers without manual intervention.

Enable it in `config/defender.php`:

```php
'blocklist' => [
    'auto_block_after'        => 5,   // Block after 5 triggered events
    'auto_block_hours'        => 24,  // Block for 24 hours (null = permanent)
    'auto_block_window_hours' => 24,  // Count events within a 24-hour sliding window
],
```

With this configuration, any IP that causes 5 blocked requests within 24 hours is automatically added to the blocklist for 24 hours. The counter resets after blocking.

Auto-block is **disabled by default** (`auto_block_after: null`).

---

## Events & Extensibility

Defender fires standard Laravel events you can listen to from your application:

```php
use Metalinked\LaravelDefender\Events\SuspiciousRequestDetected;
use Metalinked\LaravelDefender\Events\IpBlocked;

// In your EventServiceProvider or AppServiceProvider boot():
Event::listen(SuspiciousRequestDetected::class, function ($event) {
    // $event->ip, $event->reason, $event->request
    // Fired when a threat is detected, even if the request is not blocked
});

Event::listen(IpBlocked::class, function ($event) {
    // $event->ip, $event->reason, $event->request
    // Fired when a middleware actually blocks a request (returns 429 or 403)
});
```

Use these events to integrate with your own notification system, SIEM, audit trail, or any custom logic, without modifying the package.

---

## Honeypot Spam Protection

Protects forms from bots using a hidden field and a time-based check.

**1. Add the honeypot field to your Blade form:**

```blade
@defenderHoneypot
```

**2. Enable automatic protection or use the middleware per route:**

```php
// config/defender.php
'honeypot' => [
    'auto_protect_forms' => true,
],
```

Or apply it manually:

```php
Route::post('/contact', ...)->middleware('defender.honeypot');
```

**Publish the view (optional):**

```bash
php artisan vendor:publish --tag=defender-views
```

---

## Alert System

Defender supports multiple alert channels for real-time notifications.

| Channel | Description | Default |
|---------|-------------|---------|
| `log` | Writes to Laravel's application log | Enabled |
| `database` | Saves alerts to `defender_ip_logs` | Enabled |
| `mail` | Sends an email to `DEFENDER_ALERT_MAIL_TO` | Disabled |
| `slack` | Posts to a Slack webhook | Disabled |
| `webhook` | POSTs to any URL | Disabled |

---

## Laravel Pulse Card

If [Laravel Pulse](https://pulse.laravel.com/) is installed, Defender automatically registers a dashboard card with live security activity.

Add the card to your Pulse dashboard view:

```blade
<livewire:defender-pulse-card cols="4" />
```

The card shows:
- Threats detected in the last hour
- Total threats recorded
- Top 5 attacking IPs
- Latest 8 detection events (auto-refreshes every 10 seconds)

No additional configuration needed. The card is registered automatically when Pulse and Livewire are detected.

---

## Artisan Commands

### View logs

```bash
php artisan defender:ip-logs                  # Latest 50 logs
php artisan defender:ip-logs --suspicious     # Only suspicious logs
php artisan defender:ip-logs --ip=1.2.3.4     # Filter by IP
php artisan defender:ip-logs --limit=100      # Limit results
```

### Export logs

```bash
php artisan defender:export-logs --format=csv
php artisan defender:export-logs --suspicious --format=json --output=suspicious.json
php artisan defender:export-logs --ip=1.2.3.4 --from=2024-06-01 --to=2024-06-30 --format=csv
```

> Only logs stored in the database (with the `database` channel enabled) can be viewed or exported.

### Prune old logs

```bash
php artisan defender:prune-logs --days=90            # Delete logs older than 90 days
php artisan defender:prune-logs --days=30 --laravel  # Also remove old Laravel log files
```

**Scheduled pruning**: add to `bootstrap/routes/console.php`:

```php
Schedule::command('defender:prune-logs --days=90')->daily();
```

---

## Security Audit

Run a local audit of your Laravel application's security configuration:

```bash
php artisan defender:audit
```

Checks for:

- Publicly accessible `.env` file
- `APP_DEBUG` enabled in production
- Permissive CORS configuration (`allowed_origins = "*"`)
- Insecure session cookies (missing `secure` or `http_only` flags)
- Weak or missing `APP_KEY`
- Missing HTTP security headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Strict-Transport-Security`)

Each issue includes a specific remediation tip.

---

## Testing

```bash
composer test
```

> Requires the `pdo_sqlite` PHP extension. Tests use an SQLite in-memory database via Orchestra Testbench.

---

## Security

To report a security vulnerability, email [security@metalinked.net](mailto:security@metalinked.net). All reports are handled responsibly and in confidence.

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for contribution guidelines.

---

## License

MIT © [Metalinked](https://metalinked.net)

---

💬 [Questions, suggestions or feedback? Open a discussion.](https://github.com/metalinked/laravel-defender/discussions)
