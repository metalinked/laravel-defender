# TODOs & Ideas – Laravel Defender

## Release process (manual, since release-please was removed)

- Versioning, tagging and `CHANGELOG.md` entries are now done by hand (Conventional Commits style, matching the existing changelog format).

## Pending ideas

- Add configuration for custom honeypot field names (random per session)
- Consider a minimal Blade dashboard (no Livewire dependency) for viewing logs in the browser
- Filament plugin: resource + widgets for blocked IPs, logs and stats (optional integration, same pattern as the Pulse card)

## Completed

- ✅ Improve country detection and add support for more geolocation services
- ✅ Dynamic IP blocklist (defender:block-ip / unblock-ip / block-list)
- ✅ Laravel Events (SuspiciousRequestDetected, IpBlocked) for extensibility
- ✅ Laravel Pulse card for real-time security dashboard
- ✅ Security headers check in defender:audit
- ✅ Fix BruteForceMiddleware to count only suspicious requests (not all logs)
- ✅ Add cache layer to BruteForceMiddleware to reduce DB queries
- ✅ Fix missing stats translation keys (stats_ip, stats_attempts, stats_country, stats_route)
- ✅ Fix IpLoggerMiddleware not to create DB rows for every request when log_all=true
- ✅ Add auto-block option: automatically add to blocklist after N blocked requests
- ✅ Write tests for CountryAccessMiddleware (Http::fake against GeoService's provider calls)
- ✅ Add support for Laravel 12 (and later Laravel 13)
- ✅ Integrate with AbuseIPDB for IP reputation scoring, with optional auto-block above a threshold
