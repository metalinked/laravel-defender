# Upgrade Guide

## From 2.x to 3.0

3.0 makes Defender simpler to set up and to trust: one middleware, a monitor mode, a global whitelist, real login protection, and fewer false positives. Most apps need about five minutes for the upgrade.

### 1. Replace the middlewares in `bootstrap/app.php`

```php
// Before
$middleware->append(\Metalinked\LaravelDefender\Http\Middleware\BlockedIpMiddleware::class);
$middleware->append(\Metalinked\LaravelDefender\Http\Middleware\AdvancedDetectionMiddleware::class);
$middleware->append(\Metalinked\LaravelDefender\Http\Middleware\BruteForceMiddleware::class);
$middleware->append(\Metalinked\LaravelDefender\Http\Middleware\CountryAccessMiddleware::class);

// After
$middleware->append(\Metalinked\LaravelDefender\Http\Middleware\DefenderMiddleware::class);
```

Two middlewares were removed. If you reference them anywhere (including the `defender.brute_force` / `defender.iplogger` aliases in route files), remove them:

| Removed | Replaced by |
|---|---|
| `BruteForceMiddleware` (`defender.brute_force`) | `auto_block` (repeat offenders go to the blocklist) and `login_protection` (failed logins). Both work without a middleware. |
| `IpLoggerMiddleware` (`defender.iplogger`) | Nothing. Logging every request is better done with Laravel's own logging, Telescope or Pulse. |

### 2. Update `config/defender.php`

The simplest way is to republish it and copy your values back:

```bash
php artisan vendor:publish --tag=defender-config --force
```

| 2.x key | 3.0 |
|---|---|
| `ip_logging.*` | Removed |
| `brute_force.max_attempts` / `decay_minutes` | `auto_block.after` / `auto_block.window_minutes` |
| `blocklist.auto_block_after` | `auto_block.after` |
| `blocklist.auto_block_hours` | `auto_block.hours` |
| `blocklist.auto_block_window_hours` | `auto_block.window_minutes` (now in **minutes**) |
| `reputation.auto_block_hours` | `auto_block.hours` (one duration for every automatic block) |
| `country_access.whitelist_ips` | `whitelist.ips`. The old key is still honoured by country access. |
| — | New: `mode`, `whitelist`, `login_protection`, `advanced_detection.scan_body` / `extra_patterns` / `ignore_patterns`, `alerts.throttle_minutes` |

### 3. Behaviour changes to check

- **Auto-block is on by default**: 5 suspicious requests in 60 minutes block the IP for 24 hours. Set `auto_block.after` to `null` to turn it off.
- **Login protection is on by default**: 5 failed logins per IP in 15 minutes, then 429. Set `login_protection.enabled` to `false` to turn it off.
- **Status codes**: attack detection and country access now return **403** (was 429). 429 is reserved for login throttling.
- **Response body**: blocked requests get a generic "Access denied." page, or JSON when the request expects JSON, instead of the rule that matched. Customise it with `php artisan vendor:publish --tag=defender-views`.
- **Request bodies are no longer scanned** by default. Set `advanced_detection.scan_body` to `true` to restore it.
- **`curl`, `python`, `wget`, `libwww-perl` and `httpclient` are no longer in `suspicious_user_agents`** by default. Add them back if you want them blocked.
- **The built-in attack patterns changed.** Overly generic ones that matched normal text (`shell`, `passwd`, `cmd=`, `base64,`, `<!--`, `select%20`, `fuzz`) were replaced by specific ones. Patterns containing `/` (`php://`, `file://`, `etc/passwd`...) now actually match: in 2.x they never did.
- **`country_access.countries` defaults to `[]`** (was `['ES']`), and an empty list now means the feature is off.
- **The honeypot** now logs and alerts like every other protection. A rejected submission returns a 422 response instead of throwing an `HttpException`.
- **Alerts**: `mail`, `slack` and `webhook` send at most one alert per IP every 10 minutes (`alerts.throttle_minutes`, `0` to disable). The `log` channel context and the webhook `context` no longer include the `request` object: in 2.x it was serialised into the raw HTTP request, cookies and form fields included.
- **Events**: in monitor mode `IpBlocked` isn't fired (nothing is blocked); `SuspiciousRequestDetected` still is.

### 4. Published translations

If you published the translations, these keys changed:

- Removed: `alert_too_many_attempts`, `ip_blocked`, `db_logging_disabled`
- Added: `too_many_attempts`, `alert_too_many_login_attempts`
- Changed text: `access_blocked`, `auto_block_reason`
