# TODOs & Ideas – Laravel Defender

## MVP (Freemium, Offline)
- [x] Implement mail/Slack/webhook alert methods
- [x] Security audit module (env, debug, CORS, etc.)
- [~] Add filters/search to the log viewer (by IP, route, suspicious, date)
- [x] Add export to CSV/PDF for logs
- [x] Add detection for more suspicious login patterns (e.g. non-existent users, common usernames, unusual countries, suspicious user-agents, login at odd hours, etc.)
- [ ] Add configuration for custom honeypot field names
- [ ] Add option to enable/disable honeypot per route
- [x] Advanced risk pattern detection (user-agent, route, login, country/IP)
- [ ] Improve country detection and add support for more geolocation services

## General Ideas & Improvements
- [x] Add more suspicious pattern detections (e.g. path traversal, fuzzing)
- [ ] Integrate with more IP reputation services
- [x] Add multi-language support
- [ ] Add support for automatic scheduled log pruning (configurable retention period via Laravel scheduler)
- [~] Write more unit and integration tests
- [x] Make the package translatable (i18n)