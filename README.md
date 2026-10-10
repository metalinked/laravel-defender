# Laravel Defender

[![Tests](https://img.shields.io/github/actions/workflow/status/metalinked/laravel-defender/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/metalinked/laravel-defender/actions/workflows/tests.yml)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/metalinked/laravel-defender/phpstan.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/metalinked/laravel-defender/actions/workflows/phpstan.yml)
[![GitHub Release](https://img.shields.io/github/v/release/metalinked/laravel-defender?style=flat-square)](https://github.com/metalinked/laravel-defender/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/metalinked/laravel-defender?style=flat-square)](https://packagist.org/packages/metalinked/laravel-defender)
[![License](https://img.shields.io/packagist/l/metalinked/laravel-defender?style=flat-square)](https://github.com/metalinked/laravel-defender/blob/main/LICENSE.md)

Security package for Laravel. One middleware blocks scanners, attack payloads and IPs that keep misbehaving; failed logins and form spam are handled automatically. Everything runs on your own server: external services are only used for the optional geolocation and IP reputation lookups.

---

## Requirements

| Laravel | PHP   |
|---------|-------|
| 11.x    | ^8.2  |
| 12.x    | ^8.2  |
| 13.x    | ^8.3  |

---

## Quick start

**1. Install, publish the config and the migrations:**

```bash
composer require metalinked/laravel-defender
php artisan vendor:publish --tag=defender-config
php artisan vendor:publish --tag=defender-migrations
php artisan migrate
```

**2. Register the middleware globally** in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\Metalinked\LaravelDefender\Http\Middleware\DefenderMiddleware::class);
})
```

Globally, not per route: probes to URLs that don't exist in your app (`/wp-admin`, `/.env`) never reach a route, so route middleware would never see them.

**3. On a live site, start in monitor mode** (in `.env`):

```dotenv
DEFENDER_MODE=monitor
```

Defender now detects, logs and alerts, but blocks nothing. After a few days, check what it would have blocked:

```bash
php artisan defender:ip-logs --suspicious
```

No false positives? Remove the line (the default is `block`). Found some? See [Whitelist](#whitelist) and [Attack detection](#attack-detection).

> **Behind Cloudflare, a load balancer or any reverse proxy?** Read [Proxies](#behind-a-proxy-cloudflare-load-balancers) first. Without it, every visitor has the proxy's IP, and blocking one attacker blocks everyone.

---

## What it does

| Protection | Triggers when | Response |
|---|---|---|
| [Attack detection](#attack-detection) | The URL, query string or headers contain an attack payload, a scanner user-agent, or a probe route like `/wp-admin` | 403 |
| [Login protection](#login-protection) | An IP fails to log in 5 times in 15 minutes | 429 on the next attempts, *before* the password is checked |
| [Honeypot](#honeypot) | A form comes back with the hidden field filled in, or too fast | 422 |
| [Country access](#country-access) | The visitor's country isn't allowed (off until you list countries) | 403 |
| [Auto-block](#auto-block-and-blocklist) | One IP triggers any of the above 5 times in an hour | 403 for 24 hours, on every URL |
| [IP reputation](#ip-reputation-abuseipdb) | Optional: an already-flagged IP has a bad AbuseIPDB score | Score in the logs, optional block |

Every detection is logged to the `defender_ip_logs` table, sent to your [alert channels](#alerts), and fired as an [event](#events).

---

## Behind a proxy (Cloudflare, load balancers)

Defender works on `$request->ip()`. Behind a proxy that's the proxy's address unless you tell Laravel to trust it, so configure [trusted proxies](https://laravel.com/docs/requests#configuring-trusted-proxies) in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*'); // or the proxy's IP ranges
    $middleware->append(\Metalinked\LaravelDefender\Http\Middleware\DefenderMiddleware::class);
})
```

`at: '*'` is only safe when the app can't be reached except through the proxy. Otherwise, list the proxy's ranges (for Cloudflare: [cloudflare.com/ips](https://www.cloudflare.com/ips/)).

---

## Modes

```php
'mode' => env('DEFENDER_MODE', 'block'),
```

| Mode | Detects and logs | Alerts | Blocks |
|---|---|---|---|
| `block` (default) | ✓ | ✓ | ✓ |
| `monitor` | ✓ | ✓ | ✗ (and nothing is added to the blocklist) |

---

## Whitelist

Trusted traffic is never inspected, logged or blocked:

```php
'whitelist' => [
    'ips'   => ['203.0.113.7', '10.0.0.0/8'],         // single IPs or CIDR ranges
    'paths' => ['up', 'stripe/webhook', 'api/health*'], // Request::is() patterns
],
```

Use it for your office IP, health checks, and incoming webhooks from services you trust.

---

## Attack detection

Checks the URL, the query string and the request headers (except cookies) against:

- **Attack patterns**: path traversal (`../`, also URL- and double-encoded), stream wrappers (`php://`, `file://`), sensitive files (`/etc/passwd`), SQL injection (`UNION SELECT`, also with `+` or `/**/` instead of spaces), XSS (`<script`, `onerror=`), code execution (`eval(`, `<?php`), Log4Shell (`jndi:`) and scanner signatures. The full list is `AdvancedDetectionMiddleware::PATTERNS`.
- **Scanner user-agents**: `sqlmap`, `nikto`, `nmap`, `nuclei`, `wpscan`...
- **Probe routes**: `/wp-admin`, `/wp-login`, `/xmlrpc.php`, `/phpmyadmin`, `/.env`, `/.git`, `/vendor/phpunit`...
- **Common usernames** on `POST /login` (`admin`, `root`, `test`...) in the `username`, `login` or `email` field.

```php
'advanced_detection' => [
    'enabled'         => true,
    'scan_body'       => false, // also inspect POST/PUT bodies
    'extra_patterns'  => [],    // your own substrings to block
    'ignore_patterns' => [],    // built-in patterns that cause false positives for you
    'suspicious_user_agents' => ['sqlmap', 'nikto', 'nmap', /* ... */],
    'suspicious_routes'      => ['/wp-admin', '/.env', /* ... */],
    'common_usernames'       => ['admin', 'administrator', 'root', 'test', 'user'],
],
```

**Avoiding false positives:**

- Request **bodies aren't scanned by default**. Comments, rich-text editors (HTML, pasted images) and code snippets would trigger patterns all the time. Turn `scan_body` on only for apps without free-text input.
- **Generic HTTP clients aren't blocked by default** (`curl`, `python-requests`, `wget`): uptime monitors and integrations use them. Add them to `suspicious_user_agents` if you want them blocked.
- If a pattern matches legitimate traffic of yours, add it to `ignore_patterns`. Or whitelist the path.

---

## Login protection

Counts failed logins per IP through Laravel's `Attempting` and `Failed` auth events. It works with Breeze, Fortify, Jetstream and any custom controller that uses `Auth::attempt()`, with no setup.

```php
'login_protection' => [
    'enabled'       => true,
    'max_attempts'  => 5,
    'decay_minutes' => 15,
],
```

Once an IP reaches the limit, further attempts get a 429 **before** the credentials are checked, so guessing the right password after the limit doesn't get anyone in. Each refused attempt also counts towards [auto-block](#auto-block-and-blocklist).

> Logins that check passwords by hand (`Hash::check()` in an API token endpoint) don't fire auth events and aren't covered.

---

## Honeypot

A hidden field that bots fill in and humans don't, plus a minimum fill time. Add it to your forms:

```blade
<form method="POST" action="/contact">
    @csrf
    @defenderHoneypot
    ...
</form>
```

With `auto_protect_forms` on (the default), every POST in the `web` group that carries the field is checked. POSTs without the field are left alone. To *require* the field on a route, so a bot can't just leave it out, add the middleware:

```php
Route::post('/contact', ...)->middleware('defender.honeypot');
```

```php
'honeypot' => [
    'enabled'            => true,
    'auto_protect_forms' => true,
    'minimum_time'       => 2, // seconds
    'field_prefix'       => 'my_full_name_',
],
```

---

## Country access

Off until you list countries:

```php
'country_access' => [
    'mode'      => 'allow', // 'allow' = only these countries; 'deny' = block these countries
    'countries' => ['ES', 'FR'],
],

'geo' => [
    'provider'          => env('DEFENDER_GEO_PROVIDER', 'ip-api'), // 'ip-api', 'ipinfo', 'ipgeolocation'
    'cache_minutes'     => 60,
    'ipinfo_token'      => env('IPINFO_TOKEN'),
    'ipgeolocation_key' => env('IPGEOLOCATION_KEY'),
],
```

Visitors whose country can't be determined are let through, so an outage of the geo provider never locks everyone out. This includes private IPs (localhost, LAN), which are never looked up. Failed lookups are cached too, so a provider that is down isn't asked again on every request.

**Geo providers:** [ip-api.com](https://ip-api.com/) (free, no key, default), [ipinfo.io](https://ipinfo.io/) (`IPINFO_TOKEN`), [ipgeolocation.io](https://ipgeolocation.io/) (`IPGEOLOCATION_KEY`).

---

## Auto-block and blocklist

An IP that keeps triggering detections is escalated to the blocklist. From then on it gets a 403 on every URL, until the block expires:

```php
'auto_block' => [
    'after'          => 5,  // suspicious requests from one IP... (null = never)
    'window_minutes' => 60, // ...within this window
    'hours'          => 24, // block duration (null = permanent)
],

'blocklist' => [
    'enabled'   => true,
    'cache_ttl' => 300, // seconds each IP's status is cached
],
```

Manage it by hand at runtime:

```bash
php artisan defender:block-ip 1.2.3.4 --reason="Persistent attacker"  # permanent
php artisan defender:block-ip 1.2.3.4 --hours=24
php artisan defender:unblock-ip 1.2.3.4
php artisan defender:block-list
```

> **Testing locally?** Your own attacks come from `127.0.0.1` and can get it blocked. `php artisan defender:unblock-ip 127.0.0.1` lets you back in.

---

## IP reputation (AbuseIPDB)

Adds an [AbuseIPDB](https://www.abuseipdb.com/) confidence score to detections, and can block IPs with a bad score. The lookup only runs for requests **already flagged** by another protection, never on normal traffic, so the free tier (1,000 checks/day) goes a long way.

```php
'reputation' => [
    'enabled'       => true,
    'api_key'       => env('DEFENDER_ABUSEIPDB_KEY'),
    'cache_minutes' => 60,
    'threshold'     => 75,    // score (0-100) at/above which an IP is malicious
    'auto_block'    => false, // true = add those IPs to the blocklist for auto_block.hours
],
```

The score is stored in `defender_ip_logs.reputation_score` and shown in `defender:ip-logs` and `defender:stats`.

---

## Alerts

```php
'alerts' => [
    'channels'         => ['log', 'database'], // also: 'mail', 'slack', 'webhook'
    'throttle_minutes' => 10,
    'mail'    => ['to' => env('DEFENDER_ALERT_MAIL_TO')],
    'slack'   => ['webhook_url' => env('DEFENDER_SLACK_WEBHOOK')],
    'webhook' => ['url' => env('DEFENDER_ALERT_WEBHOOK')],
],
```

| Channel | What it does |
|---|---|
| `log` | Writes to Laravel's log |
| `database` | Saves to `defender_ip_logs` (used by `defender:ip-logs`, `defender:stats` and the Pulse card) |
| `mail` | Emails `DEFENDER_ALERT_MAIL_TO` |
| `slack` | Posts to a Slack incoming webhook |
| `webhook` | POSTs JSON (`subject`, `message`, `context`) to any URL |

`mail`, `slack` and `webhook` send **at most one alert per IP** every `throttle_minutes`, so a scan of 500 requests is one email, not 500. `log` and `database` record every hit.

Alerts contain the IP, method, path, user-agent, reason and reputation score. They never include cookies, headers or form fields.

---

## Blocked responses

A blocked visitor gets a minimal "Access denied." page (or `{"message": "Access denied."}` when the request expects JSON). The response deliberately doesn't say which rule matched: that would only help an attacker get around it. The reason is in your logs.

To customise the page, publish the views and edit `resources/views/vendor/defender/blocked.blade.php`:

```bash
php artisan vendor:publish --tag=defender-views
```

---

## Events

```php
use Metalinked\LaravelDefender\Events\IpBlocked;
use Metalinked\LaravelDefender\Events\SuspiciousRequestDetected;

Event::listen(SuspiciousRequestDetected::class, function ($event) {
    // $event->ip, $event->reason, $event->request
    // Every detection, also in monitor mode
});

Event::listen(IpBlocked::class, function ($event) {
    // $event->ip, $event->reason, $event->request
    // Only when a request was actually refused
});
```

---

## Individual middlewares

`DefenderMiddleware` (alias `defender`) runs, in order: the blocklist, attack detection and country access. To use them separately, they're also available on their own: `defender.blocked`, `defender.advanced_detection`, `defender.country_access`, and `defender.honeypot` (honeypot required on that route).

---

## Laravel Pulse card

If [Laravel Pulse](https://pulse.laravel.com/) and Livewire are installed, Defender registers a dashboard card automatically. Add it to your Pulse dashboard:

```blade
<livewire:defender-pulse-card cols="4" />
```

It shows threats in the last hour, total threats, the top 5 attacking IPs and the latest 8 detections, refreshing every 10 seconds.

---

## Artisan commands

```bash
php artisan defender:ip-logs                  # latest 50 logs (--suspicious, --ip=1.2.3.4, --limit=100)
php artisan defender:stats                    # totals and top IPs, countries, routes, reputation scores
php artisan defender:export-logs --format=csv # or json (--ip, --from, --to, --output)
php artisan defender:prune-logs --days=90     # --laravel also deletes old Laravel log files
php artisan defender:block-ip / unblock-ip / block-list
php artisan defender:audit                    # security audit, see below
```

Prune logs on a schedule in `routes/console.php`:

```php
Schedule::command('defender:prune-logs --days=90')->daily();
```

---

## Security audit

```bash
php artisan defender:audit
```

Checks for a publicly accessible `.env`, `APP_DEBUG` on, permissive CORS, insecure session cookies, a weak `APP_KEY`, and missing security headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Strict-Transport-Security`). Each issue comes with a fix.

---

## Environment variables

| Variable | Description |
|---|---|
| `DEFENDER_MODE` | `block` (default) or `monitor` |
| `DEFENDER_GEO_PROVIDER` | `ip-api`, `ipinfo` or `ipgeolocation` |
| `IPINFO_TOKEN` | API token for ipinfo.io |
| `IPGEOLOCATION_KEY` | API key for ipgeolocation.io |
| `DEFENDER_ABUSEIPDB_KEY` | API key for AbuseIPDB |
| `DEFENDER_ALERT_MAIL_TO` | Email address for alerts |
| `DEFENDER_SLACK_WEBHOOK` | Slack incoming webhook URL |
| `DEFENDER_ALERT_WEBHOOK` | Webhook URL for alerts |

---

## Upgrading

From 2.x to 3.0: see [UPGRADE.md](UPGRADE.md).

---

## Testing

```bash
composer test
```

> Requires the `sqlite3` and `pdo_sqlite` PHP extensions. Tests use an SQLite in-memory database via Orchestra Testbench.

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

Questions or suggestions: [open a discussion](https://github.com/metalinked/laravel-defender/discussions).
