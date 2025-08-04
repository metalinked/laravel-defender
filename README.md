# Laravel Defender

A modular security package for Laravel that helps you monitor, detect, and block suspicious or malicious activity in your applications.  
Laravel Defender offers advanced request logging, risk pattern detection, brute force and spam protection, and real-time alerts—all fully configurable and privacy-friendly.  
Easily integrate Defender into your Laravel projects to enhance your application's security with flexible, modern tools.

> ℹ️ Actively maintained. Feedback and contributions are welcome.

> **Note:**  
> This package is 100% open source and does not connect to any external service by default.

---

## ✨ Features

- 🛡️ Honeypot-based spam protection for forms  
- 👁️ Request logging and alert system for suspicious activity  
- 📝 View logs and alerts via Artisan command
- ⚙️ Customizable rules and middleware  
- 🚨 **Advanced risk pattern detection** (user-agents, routes, login attempts, country/IP restrictions, path traversal, fuzzing)
- 🔔 Local real-time alerts (log, mail, Slack, webhook)
- 🔍 Security audit command for common Laravel misconfigurations

---

## 🚀 Installation

```bash
composer require metalinked/laravel-defender
```

After installation, publish the config file:

```bash
php artisan vendor:publish --tag=defender-config
```

> **Note:**  
> The `database` channel is optional, but enabled by default in the alert system.  
> Only publish and run the migration if you want to keep database logging enabled (see the `alerts.channels` option in `config/defender.php`).  
> If you disable the `database` channel, you do not need to publish or run the migration, and no logs will be stored in the database.

**Publish the migration file:**

```bash
php artisan vendor:publish --tag=defender-migrations
```

**Run the migrations:**

```bash
php artisan migrate
```

---

## 🔒 Global Protection (Recommended)

To ensure Defender can detect and block a wide range of suspicious and malicious access attempts—including requests to non-existent routes (such as `/wp-admin`, `/phpmyadmin`, `/xmlrpc.php`), brute force attacks, access from non-allowed countries, and risky login patterns, you should register all Defender middlewares as global middlewares:

- **IpLoggerMiddleware**: logs all requests if the `ip_logging.log_all` option is enabled in the configuration.
- **AdvancedDetectionMiddleware**: detects suspicious user-agents, common attack routes, and login attempts with common usernames.
- **BruteForceMiddleware**: detects and blocks brute force attempts from the same IP.
- **CountryAccessMiddleware**: allows or denies access based on country or IP whitelist/denylist.

Registering these middlewares globally ensures your application is protected against a broad spectrum of attacks, including those targeting non-existent or sensitive routes.

### For Laravel 11 or higher

Add the following to your `bootstrap/app.php` inside the `withMiddleware` callback:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\Metalinked\LaravelDefender\Http\Middleware\AdvancedDetectionMiddleware::class);
    $middleware->append(\Metalinked\LaravelDefender\Http\Middleware\BruteForceMiddleware::class);
    $middleware->append(\Metalinked\LaravelDefender\Http\Middleware\CountryAccessMiddleware::class);
})
```

### For Laravel 10 and earlier

Add the following to the `$middleware` array in your `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...existing Laravel middleware...
    \Metalinked\LaravelDefender\Http\Middleware\AdvancedDetectionMiddleware::class,
    \Metalinked\LaravelDefender\Http\Middleware\BruteForceMiddleware::class,
    \Metalinked\LaravelDefender\Http\Middleware\CountryAccessMiddleware::class,
];
```

> **Recommended:**  
> Registering these middlewares globally ensures all requests are protected, including non-existent routes, without needing to add them to individual routes.

---

## 🛡️ Honeypot Spam Protection

This package provides configurable honeypot protection for your Laravel forms.

### Quick start

1. **Publish the Blade view (optional):**
   ```bash
   php artisan vendor:publish --tag=defender-views
   ```

2. **Add the honeypot field to your forms:**
   ```blade
   @defenderHoneypot
   ```

3. **Configure automatic protection (optional):**
   In `config/defender.php`, set:
   ```php
   'honeypot' => [
       'auto_protect_forms' => true, // or false for manual middleware
       // ...other options
   ],
   ```

4. **Manual middleware (if auto protection is disabled):**
   Add the middleware to your route:
   ```php
   Route::post('/your-form', ...)->middleware('defender.honeypot');
   ```

---

## 🚨 Advanced Risk Pattern Detection

Laravel Defender can detect and alert on suspicious patterns beyond just IPs.

### What is detected?

- **Suspicious user-agents:** (e.g. curl, python, sqlmap, scanner, etc.)
- **Access to common attack routes:** `/wp-admin`, `/phpmyadmin`, `/xmlrpc.php`, etc.
- **Login attempts with common usernames:** `admin`, `root`, `test`, etc.
- **Access from blocked or non-allowed countries:** (with free IP geolocation)
- **Brute force attempts:** Too many requests from the same IP in a short period
- **Path traversal and fuzzing patterns:** Attempts to exploit with `../`, encoded traversal, or common fuzzing payloads/tools (e.g. sqlmap, acunetix, etc.)

### How to configure

In your `config/defender.php`:

```php
'advanced_detection' => [
    'enabled' => true,
    'geo_provider' => 'ip-api', // 'ip-api', 'ipinfo', 'ipgeolocation'
    'geo_cache_minutes' => 10, // Cache country codes for 10 minutes
    'ipinfo_token' => env('IPINFO_TOKEN'), // API token for ipinfo.io
    'ipgeolocation_key' => env('IPGEOLOCATION_KEY'), // API key for ipgeolocation.io
    'suspicious_user_agents' => [
        'curl', 'python', 'sqlmap', 'nmap', 'nikto', 'fuzzer', 'scanner'
    ],
    'suspicious_routes' => [
        '/wp-admin', '/wp-login', '/phpmyadmin', '/admin.php', '/xmlrpc.php'
    ],
    'common_usernames' => [
        'admin', 'administrator', 'root', 'test', 'user'
    ],
    'country_access' => [
        'mode' => 'allow', // 'allow': only allow these countries, 'deny': block these countries
        'countries' => ['ES'],
        'whitelist_ips' => ['1.2.3.4'], // Always allowed, regardless of country/mode
    ],
],
```

**Note:**  
- You can set `mode` to `'allow'` (only allow listed countries) or `'deny'` (block listed countries).
- IPs in `whitelist_ips` are always allowed, regardless of country or mode.
- Country detection supports multiple providers:
  - [ip-api.com](https://ip-api.com/) (free tier, no registration required, default)
  - [ipinfo.io](https://ipinfo.io/) (requires API token for production use)
  - [ipgeolocation.io](https://ipgeolocation.io/) (requires API key)

---

## 🔔 Alert System

Laravel Defender supports local real-time alerts via multiple channels.

### Supported channels

- `log` (Laravel log)
- `database` (save to the database)
- `mail` (send to a configured email)
- `slack` (send to a Slack webhook)
- `webhook` (send to any external URL)

> Only the `log` and `database` channels are enabled by default.  

### How to configure

In your `config/defender.php`:

```php
'alerts' => [
    'channels' => [
        'log',      // Always enabled by default
        'database', // Enabled to save to the database
        // 'mail',   // Enable to receive email alerts
        // 'slack',  // Enable to receive Slack alerts
        // 'webhook' // Enable to receive alerts via webhook
    ],
    'mail' => [
        'to' => env('DEFENDER_ALERT_MAIL_TO', null),
    ],
    'slack' => [
        'webhook_url' => env('DEFENDER_SLACK_WEBHOOK', null),
    ],
    'webhook' => [
        'url' => env('DEFENDER_ALERT_WEBHOOK', null),
    ],
],
```

---

## Environment Variables

You can configure Laravel Defender using the following `.env` variables:

| Variable                    | Description                                      | Example                        |
|-----------------------------|--------------------------------------------------|--------------------------------|
| DEFENDER_GEO_PROVIDER       | Geolocation provider (ip-api, ipinfo, ipgeolocation) | `DEFENDER_GEO_PROVIDER=ipinfo` |
| IPINFO_TOKEN                | API token for ipinfo.io geolocation service      | `IPINFO_TOKEN=abcd1234`        |
| IPGEOLOCATION_KEY           | API key for ipgeolocation.io service             | `IPGEOLOCATION_KEY=abcd1234`   |
| DEFENDER_ALERT_MAIL_TO      | Email address to receive alert notifications     | `DEFENDER_ALERT_MAIL_TO=admin@example.com` |
| DEFENDER_SLACK_WEBHOOK      | Slack webhook URL for alert notifications        | `DEFENDER_SLACK_WEBHOOK=https://hooks.slack.com/services/XXX/YYY/ZZZ` |
| DEFENDER_ALERT_WEBHOOK      | External webhook URL for alert notifications     | `DEFENDER_ALERT_WEBHOOK=https://yourdomain.com/defender-webhook` |

> All variables are optional and only required if you enable the corresponding alert channel or feature in `config/defender.php`.

---

## 📝 IP Logging & Brute Force Protection

You can control global request logging and brute force protection in your `config/defender.php`:

```php
'ip_logging' => [
    'log_all' => false, // WARNING: If true, logs ALL requests (not just suspicious ones).
                        // Only recommended for testing or temporary auditing.
                        // Not suitable for production environments!
],

'brute_force' => [
    'max_attempts' => 5,
    'decay_minutes' => 10,
],
```

- `ip_logging.log_all`: If set to `true`, logs every request (not just suspicious ones).  
  **Warning:** Only enable this for testing or temporary audits. Not recommended for production!
- `brute_force.max_attempts`: Number of allowed attempts before blocking an IP.
- `brute_force.decay_minutes`: Time window for counting attempts.

---

## 📊 Viewing and Exporting IP Logs and Alerts

Laravel Defender provides an Artisan command to review access logs and suspicious activity directly from the console.  

> **Important:**  
> Only logs stored in the database (with the `database` alert channel enabled and migration run) can be viewed or exported using these commands.  
> Logs written to the Laravel log file (`storage/logs/laravel.log`) are not accessible via Defender commands.

This approach is secure and convenient, as it does not expose sensitive data via the web and works even if your app does not have a backoffice.

> **Note:**  
> Viewing and exporting logs is only available if the `database` channel is enabled and the migration has been run.

### Usage

Show the latest 50 logs:
```sh
php artisan defender:ip-logs
```

Show only suspicious logs:
```sh
php artisan defender:ip-logs --suspicious
```

Filter by IP:
```sh
php artisan defender:ip-logs --ip=1.2.3.4
```

Limit the number of results:
```sh
php artisan defender:ip-logs --limit=100
```

You can combine options as needed.

---

### Export logs to CSV or JSON

Export all logs to CSV:
```sh
php artisan defender:export-logs --format=csv
```

Export only suspicious logs to JSON:
```sh
php artisan defender:export-logs --suspicious --format=json --output=suspicious-logs.json
```

Export logs for a specific IP and date range:
```sh
php artisan defender:export-logs --ip=1.2.3.4 --from=2024-06-01 --to=2024-06-09 --format=csv --output=logs.csv
```

---

## 🧹 Pruning Old Logs

You can easily clean up old logs from the database (and optionally from Laravel log files) using the built-in Artisan command:

Delete Defender logs older than 90 days from the database:
```sh
php artisan defender:prune-logs --days=90
```

Delete Defender logs older than 30 days and also remove old Laravel log files:
```sh
php artisan defender:prune-logs --days=30 --laravel
```

> **Note:**  
> Only logs stored in the database can be listed and exported with Defender commands.  
> Logs written to the Laravel log file (`storage/logs/laravel.log`) are not accessible via Defender commands and must be managed manually or with the `--laravel` prune option.

### Scheduled log pruning

To automatically prune old Defender logs on a schedule, add the following to your scheduler file:

For Laravel 11 and newer (`bootstrap/routes/console.php`):

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('defender:prune-logs --days=90')->daily();
```

For Laravel 10 and earlier (`app/Console/Kernel.php`):

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('defender:prune-logs --days=90')->daily();
}
```

This will delete Defender logs older than 90 days every day.  
You can adjust the frequency and retention period as needed.

---

## 🔒 Security Audit

Run a local security audit of your Laravel project:

```sh
php artisan defender:audit
```

This command checks for:
- Publicly accessible `.env` file
- APP_DEBUG enabled
- Permissive CORS configuration
- Insecure session cookies
- Laravel version

It gives clear recommendations for each issue found.

---

## 🧪 Testing

Run tests with:

```bash
composer test
```

Or if using Pest:

```bash
./vendor/bin/pest
```

> **Note:**  
> Make sure your PHP installation has the `sqlite3` and `pdo_sqlite` extensions enabled.  
> These are required for running the package tests (Testbench uses SQLite in-memory by default).

---

## 🛡️ Security

If you discover a security vulnerability, please report it via email to [security@metalinked.net](mailto:security@metalinked.net). All reports will be handled responsibly and in confidence.

---

## Usage Model

- **Free & Open Source (offline):**  
  All users can use the basic security features locally, without connecting to any external service. No registration required. Privacy-friendly and self-hosted.

---

## 🤝 Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on how to contribute.

---

## 📄 License

MIT © [Metalinked](https://metalinked.net)

---

## 📢 Stay in touch

If you're interested in using this tool or contributing, feel free to open an issue or start a discussion.

💬 [Questions, suggestions or feedback? Join the Discussions!](https://github.com/metalinked/laravel-defender/discussions)