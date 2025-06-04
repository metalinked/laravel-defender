# TODOs & Ideas – Laravel Defender

## IP Logging & Alert Manager
- [ ] Implement mail alert method
- [ ] Implement Slack alert method
- [ ] Implement webhook alert method
- [x] Add a protected route or Artisan command to view IP logs and alerts
- [ ] Add filters/search to the log viewer (by IP, route, suspicious, date)
- [ ] Add export to CSV/PDF for logs
- [ ] Add detection for more suspicious login patterns (e.g. non-existent users, common usernames, unusual countries, suspicious user-agents, login at odd hours, etc.)

## Honeypot Protection
- [ ] Add configuration for custom honeypot field names
- [ ] Add option to enable/disable honeypot per route

## Dashboard
- [ ] Create a professional dashboard UI for logs and alerts
- [ ] Add statistics and charts (e.g. attempts per day, top IPs)
- [ ] Add authentication/token/IP protection for dashboard access
- [ ] Make dashboard themeable/customizable

## SaaS Integration (Premium/Future)
- [ ] Implement SaaS connection logic (API endpoint, token)
- [ ] Add remote log/alert sync
- [ ] Add SaaS onboarding and registration flow
- [ ] Add SaaS-based advanced features (attack signatures, AI scoring, etc.)

## General Ideas & Improvements
- [ ] Add more suspicious pattern detections (e.g. path traversal, fuzzing)
- [ ] Integrate with more IP reputation services
- [ ] Add client fingerprinting (IP, UA, headers, timezone, etc.)
- [ ] Add multi-language support for dashboard and alerts
- [ ] Write more unit and integration tests

---