<?php

namespace Metalinked\LaravelDefender\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Metalinked\LaravelDefender\Defender;
use Metalinked\LaravelDefender\Detection\GeoService;
use Metalinked\LaravelDefender\Detection\ReputationService;
use Metalinked\LaravelDefender\Events\SuspiciousRequestDetected;
use Metalinked\LaravelDefender\Models\IpLog;

class AlertManager {
    public static function send(string $subject, string $message, array $context = []): void {
        $channels = config('defender.alerts.channels', []);
        $request = ($context['request'] ?? null) instanceof Request ? $context['request'] : null;
        $isSuspicious = (bool) ($context['is_suspicious'] ?? false);
        $ip = $request?->ip() ?? ($context['ip'] ?? null);

        $reputationScore = ($isSuspicious && $ip) ? ReputationService::getScore($ip) : null;

        // What leaves the app (log file, Slack, webhooks). Never the Request object itself:
        // Monolog stringifies it into the raw HTTP request, cookies and form fields included.
        $payload = array_filter([
            'ip' => $ip,
            'method' => $request?->method(),
            'route' => $request?->path() ?? ($context['route'] ?? null),
            'user_agent' => $request?->userAgent(),
            'reason' => $context['reason'] ?? $message,
            'reputation_score' => $reputationScore,
            'mode' => config('defender.mode', 'block'),
        ], fn ($value) => $value !== null);

        if (in_array('log', $channels)) {
            Log::warning("[Defender] $subject: $message", $payload);
        }

        if (in_array('database', $channels) && $request && Schema::hasTable((new IpLog)->getTable())) {
            IpLog::create([
                'ip' => $request->ip(),
                'route' => $request->path(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'is_suspicious' => $isSuspicious,
                'reason' => mb_substr((string) ($context['reason'] ?? 'alert'), 0, 255),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 512) ?: null,
                'referer' => mb_substr((string) $request->headers->get('referer'), 0, 512) ?: null,
                'country_code' => GeoService::getCountryCode((string) $request->ip()),
                'headers_hash' => hash('sha256', json_encode($request->headers->all())),
                'reputation_score' => $reputationScore,
            ]);
        }

        if (self::shouldNotify($ip)) {
            self::notify($channels, $subject, $message, $payload);
        }

        if ($isSuspicious && $ip) {
            self::autoBlockByReputation($ip, $reputationScore);
        }

        if ($isSuspicious && $request) {
            event(new SuspiciousRequestDetected($request, (string) $ip, $context['reason'] ?? $message));
        }
    }

    /**
     * mail/slack/webhook: at most one alert per IP per alerts.throttle_minutes.
     */
    protected static function shouldNotify(?string $ip): bool {
        $minutes = (int) config('defender.alerts.throttle_minutes', 10);

        if ($minutes <= 0 || ! $ip) {
            return true;
        }

        return Cache::add("defender:alert_sent:{$ip}", true, now()->addMinutes($minutes));
    }

    protected static function notify(array $channels, string $subject, string $message, array $payload): void {
        $to = config('defender.alerts.mail.to');
        if (in_array('mail', $channels) && $to) {
            $body = $message . "\n\n" . collect($payload)->map(fn ($v, $k) => "$k: $v")->implode("\n");
            Mail::raw($body, fn ($mail) => $mail->to($to)->subject($subject));
        }

        $slackUrl = config('defender.alerts.slack.webhook_url');
        if (in_array('slack', $channels) && $slackUrl) {
            try {
                Http::timeout(5)->post($slackUrl, [
                    'text' => "[Defender] $subject\n$message (IP: " . ($payload['ip'] ?? '-') . ')',
                ]);
            } catch (\Exception $e) {
                Log::error('Defender Slack alert failed: ' . $e->getMessage());
            }
        }

        $webhookUrl = config('defender.alerts.webhook.url');
        if (in_array('webhook', $channels) && $webhookUrl) {
            try {
                Http::timeout(5)->post($webhookUrl, [
                    'subject' => $subject,
                    'message' => $message,
                    'context' => $payload,
                ]);
            } catch (\Exception $e) {
                Log::error('Defender webhook alert failed: ' . $e->getMessage());
            }
        }
    }

    protected static function autoBlockByReputation(string $ip, ?int $score): void {
        if (
            $score === null
            || ! config('defender.reputation.auto_block', false)
            || $score < (int) config('defender.reputation.threshold', 75)
            || Defender::monitoring()
            || BlocklistService::isBlocked($ip)
        ) {
            return;
        }

        BlocklistService::block(
            $ip,
            __('defender::defender.reputation_block_reason', ['score' => $score]),
            BlocklistService::autoBlockUntil(),
        );
    }
}
