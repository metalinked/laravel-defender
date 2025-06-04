# TODOs & Ideas – Laravel Defender

## MVP (Freemium, Offline)
- [ ] Implement mail/Slack/webhook alert methods
- [ ] Security audit module (env, debug, CORS, etc.)
- [ ] Add filters/search to the log viewer (by IP, route, suspicious, date)
- [ ] Add export to CSV/PDF for logs
- [ ] Add detection for more suspicious login patterns (e.g. non-existent users, common usernames, unusual countries, suspicious user-agents, login at odd hours, etc.)
- [ ] Add configuration for custom honeypot field names
- [ ] Add option to enable/disable honeypot per route
- [ ] Security audit module (env, debug, CORS, etc.)
- [ ] Privacy-friendly client fingerprinting (IP, UA, headers, timezone, etc.)

## SaaS Integration (Freemium/Premium)
- [ ] Implement SaaS connection logic (API endpoint, token)
- [ ] Add remote log/alert sync
- [ ] Add SaaS onboarding and registration flow
- [ ] Centralized SaaS dashboard (1 project free)
- [ ] Token management and activation flow
- [ ] Enforce project limits and feature access based on SaaS plan (free/premium)
- [ ] Allow users to generate and manage tokens from the SaaS dashboard

## Premium Features (SaaS only)
- [ ] Advanced attack signatures (JSON-based rule engine, regex matching)
- [ ] AI-powered risk scoring for suspicious requests
- [ ] Integration with external IP reputation services (AbuseIPDB, Project Honeypot, etc.)
- [ ] API endpoints for enterprise clients (event/alert consumption)
- [ ] Multi-project management
- [ ] Security audit module with PDF reports (correlate logs with public CVEs)

## General Ideas & Improvements
- [ ] Add more suspicious pattern detections (e.g. path traversal, fuzzing)
- [ ] Integrate with more IP reputation services
- [ ] Add multi-language support for dashboard and alerts
- [ ] Write more unit and integration tests