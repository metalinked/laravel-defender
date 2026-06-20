# TODOs & Ideas – Laravel Defender

## Pending ideas

- Add configuration for custom honeypot field names (random per session)
- Integrate with more IP reputation services (AbuseIPDB, Shodan)
- Write tests for CountryAccessMiddleware with mocked GeoService
- Add auto-block option: automatically add to blocklist after N blocked requests
- Consider a minimal Blade dashboard (no Livewire dependency) for viewing logs in the browser
- Add support for Laravel 12

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
