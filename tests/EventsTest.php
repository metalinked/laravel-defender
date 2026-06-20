<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Events\IpBlocked;
use Metalinked\LaravelDefender\Events\SuspiciousRequestDetected;

class EventsTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        Route::middleware('advanced.detection')->post('/test-events', function () {
            return response('OK');
        });
    }

    public function test_suspicious_request_detected_event_is_fired(): void {
        Event::fake([SuspiciousRequestDetected::class, IpBlocked::class]);

        $this->post('/test-events', [], ['User-Agent' => 'sqlmap/1.0']);

        Event::assertDispatched(SuspiciousRequestDetected::class, function ($event) {
            return $event->ip === '127.0.0.1'
                && str_contains($event->reason, 'sqlmap');
        });
    }

    public function test_ip_blocked_event_is_fired_on_block(): void {
        Event::fake([IpBlocked::class]);

        $this->post('/test-events', [], ['User-Agent' => 'sqlmap/1.0']);

        Event::assertDispatched(IpBlocked::class, function ($event) {
            return $event->ip === '127.0.0.1';
        });
    }

    public function test_no_events_fired_for_clean_request(): void {
        Event::fake([SuspiciousRequestDetected::class, IpBlocked::class]);

        $this->post('/test-events', [], ['User-Agent' => 'Mozilla/5.0']);

        Event::assertNotDispatched(SuspiciousRequestDetected::class);
        Event::assertNotDispatched(IpBlocked::class);
    }
}
