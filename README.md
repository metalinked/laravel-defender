# Laravel Defender

A modular security package for Laravel designed to help you monitor and protect your Laravel applications with tools similar to Wordfence in the WordPress ecosystem.

> ⚠️ **Currently under development**. Contributions and feedback are welcome.

---

## ✨ Features (Planned)

- 🛡️ Honeypot-based spam protection for forms  
- 👁️ Request logging and alert system for suspicious activity  
- ☁️ Centralized SaaS control panel (optional)  
- ⚙️ Customizable rules and middleware  
- 🔐 Token-based authentication for external API reporting  
- 🎛️ Optional Laravel Nova/Telescope integration  

---

## 🚀 Installation

_Not yet available on Packagist_

```bash
composer require metalinked/laravel-defender
```

---

## 📦 Usage

Basic usage examples will be added as the package stabilizes. Planned usage will include:

- Publishing config files  
- Using middleware and Blade components  
- Dashboard access from your app (or SaaS)  

---

## 🛡️ Honeypot Spam Protection

This package provides configurable honeypot protection for your Laravel forms.

### Quick start

1. **Publish the config and Blade view (optional):**
   ```bash
   php artisan vendor:publish --tag=defender-config
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

### How it works

- The honeypot adds hidden fields to your forms via the Blade directive.
- Submissions that fill the honeypot field or submit too quickly are blocked with a 422 error.
- All options are configurable in `config/defender.php`.

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

---

## 🛡️ Security

If you discover a security vulnerability, please report it via email to [security@metalinked.dev](mailto:security@metalinked.dev). All reports will be handled responsibly and in confidence.

---

## 📍 Roadmap

### MVP
- [x] Project bootstrapped
- [x] Honeypot spam protection
- [ ] IP logging and alert manager
- [ ] Local dashboard (optional)
- [ ] Basic SaaS API connection

### Freemium & Premium Features
- [ ] Advanced attack signatures (JSON-based rule engine, regex matching)
- [ ] AI-powered risk scoring for suspicious requests
- [ ] Integration with external IP reputation services (AbuseIPDB, Project Honeypot, etc.)
- [ ] Instant notifications (Telegram, Slack, email)
- [ ] Security audit module with PDF reports (correlate logs with public CVEs)
- [ ] Automated onboarding and security baseline checks (exposed .env, APP_DEBUG, CORS, cookies, etc.)
- [ ] Centralized SaaS dashboard (separate from client app)
- [ ] API endpoints for enterprise clients (event/alert consumption)
- [ ] Privacy-friendly client fingerprinting (IP, UA, headers, timezone, etc.)

---

**This roadmap is inspired by expert security feedback and aims to make Laravel Defender a truly advanced, modern, and extensible security solution for Laravel and beyond.**

---

## 🤝 Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on how to contribute.

---

## 📄 License

MIT © [Metalinked](https://metalinked.dev)

---

## 📢 Stay in touch

If you're interested in using this tool or contributing, feel free to open an issue or start a discussion.
