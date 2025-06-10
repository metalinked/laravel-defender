# Laravel Defender

A modular security package for Laravel designed to help you monitor and protect your Laravel applications with tools similar to Wordfence in the WordPress ecosystem.

> ⚠️ **Currently under development**. Contributions and feedback are welcome.

> **Note:**  
> This package is 100% open source and does not connect to any external service by default.  
> In the future, an optional SaaS connector will be available as a separate package to unlock advanced features.

---

## ✨ Features

- 🛡️ Honeypot-based spam protection for forms  
- 👁️ Request logging and alert system for suspicious activity  
- 📝 View logs and alerts via Artisan command
- ⚙️ Customizable rules and middleware  
- 🚨 **Advanced risk pattern detection** (user-agents, routes, login attempts, country/IP restrictions)
- 🔔 Local real-time alerts (log, mail, Slack, webhook)
- 🔍 Security audit command for common Laravel misconfigurations
- 🎛️ Optional Laravel Nova/Telescope integration (planned)

---

## 🚀 Installation

_Not yet available on Packagist_

```bash
composer require metalinked/laravel-defender
```

After installation, publish the config file:

```bash
php artisan vendor:publish --tag=defender-config
```

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

### How to configure

In your `config/defender.php`:

```php
'advanced_detection' => [
    'enabled' => true,
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
- Country detection uses [ip-api.com](https://ip-api.com/) (free tier, no registration required).

---

## 🔔 Alert System

Laravel Defender supports local real-time alerts via multiple channels.

### Supported channels

- `log` (Laravel log)
- `mail` (send to a configured email)
- `slack` (send to a Slack webhook)
- `webhook` (send to any external URL)

### How to configure

In your `config/defender.php`:

```php
'alerts' => [
    'channels' => [
        'log',      // Always enabled by default
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

#### To receive email alerts

Add this to your `.env` file:

```
DEFENDER_ALERT_MAIL_TO=your@email.com
```

---

## 📊 Viewing and Exporting IP Logs and Alerts

Laravel Defender provides an Artisan command to review access logs and suspicious activity directly from the console.  
This approach is secure and convenient, as it does not expose sensitive data via the web and works even if your app does not have a backoffice.

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

## 📦 Usage

Basic usage examples will be added as the package stabilizes. Planned usage will include:

- Publishing config files  
- Using middleware and Blade components 

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

- **Freemium (offline):**  
  All users can use the basic security features locally, without connecting to any external service. No registration required.

- **Freemium (SaaS-connected):** _[Planned]_  
  Users will optionally be able to register on the SaaS platform to monitor and manage up to 1 Laravel project for free, with centralized logs and a basic dashboard.

- **Premium (SaaS-connected):** _[Planned]_  
  Paid plans will unlock premium features (AI risk scoring, advanced signatures, multi-project management, etc.) and/or allow monitoring more Laravel projects from the SaaS dashboard. Each project will be linked to a unique token generated in the SaaS panel.

> **Note:**  
> All SaaS and premium features will be provided via a separate connector package in the future.  
> This open source package does **not** connect to any external service by default.

---

## 📍 Roadmap

### MVP (Freemium, Offline)
- [x] IP logging & alert manager
- [x] Honeypot spam protection
- [x] Local notifications (log, mail, Slack, webhook)
- [x] Security audit module (env, debug, CORS, etc.)
- [x] Advanced risk pattern detection (user-agent, route, login, country/IP)
- [ ] Privacy-friendly client fingerprinting (IP, UA, headers, timezone, etc.)
- [x] Export logs to CSV/JSON

### SaaS Integration (Freemium/Premium) — _via separate connector package_
- [ ] Basic SaaS API connection (token-based)
- [ ] Centralized SaaS dashboard (1 project free)
- [ ] Token management and activation flow

### Premium Features (SaaS only)
- [ ] Advanced attack signatures (JSON-based rule engine, regex matching)
- [ ] AI-powered risk scoring for suspicious requests
- [ ] Integration with external IP reputation services (AbuseIPDB, Project Honeypot, etc.)
- [ ] API endpoints for enterprise clients (event/alert consumption)
- [ ] Multi-project management
- [ ] Security audit module with PDF reports (correlate logs with public CVEs)

---

**This roadmap is inspired by expert security feedback and aims to make Laravel Defender a truly advanced, modern, and extensible security solution for Laravel and beyond.**

---

## 🤝 Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on how to contribute.

---

## 📄 License

MIT © [Metalinked](https://metalinked.net)

---

## 📢 Stay in touch

If you're interested in using this tool or contributing, feel free to open an issue or start a discussion.