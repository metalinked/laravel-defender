<?php

namespace Metalinked\LaravelDefender\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Metalinked\LaravelDefender\Detection\GeoService;
use Metalinked\LaravelDefender\Events\SuspiciousRequestDetected;
use Metalinked\LaravelDefender\Models\IpLog;

class AlertManager {
    public static function send(string $subject, string $message, array $context = []): void {
        $channels = config('defender.alerts.channels', []);

        if (in_array('log', $channels)) {
            Log::warning("[Defender] $subject: $message", $context);
        }

        $to = config('defender.alerts.mail.to');
        if (in_array('mail', $channels) && $to) {
            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)->subject($subject);
            });
        }

        if (in_array('database', $channels) && isset($context['request'])) {
            $table = (new IpLog)->getTable();
            if (Schema::hasTable($table)) {
                $request = $context['request'];
                IpLog::create([
                    'ip' => $request->ip(),
                    'route' => $request->path(),
                    'method' => $request->method(),
                    'user_id' => auth()->check() ? auth()->id() : null,
                    'is_suspicious' => $context['is_suspicious'] ?? true,
                    'reason' => $context['reason'] ?? 'alert',
                    'user_agent' => $request->userAgent(),
                    'referer' => $request->headers->get('referer'),
                    'country_code' => GeoService::getCountryCode($request->ip()),
                    'headers_hash' => hash('sha256', json_encode($request->headers->all())),
                ]);
            }
        }

        if (in_array('slack', $channels)) {
            $slackUrl = config('defender.alerts.slack.webhook_url');
            if ($slackUrl) {
                try {
                    \Illuminate\Support\Facades\Http::post($slackUrl, [
                        'text' => "[Defender] $subject\n$message",
                    ]);
                } catch (\Exception $e) {
                    Log::error('Defender Slack alert failed: ' . $e->getMessage());
                }
            }
        }

        if (in_array('webhook', $channels)) {
            $webhookUrl = config('defender.alerts.webhook.url');
            if ($webhookUrl) {
                try {
                    \Illuminate\Support\Facades\Http::post($webhookUrl, [
                        'subject' => $subject,
                        'message' => $message,
                        'context' => $context,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Defender webhook alert failed: ' . $e->getMessage());
                }
            }
        }

        if (($context['is_suspicious'] ?? false) && isset($context['request'])) {
            event(new SuspiciousRequestDetected(
                $context['request'],
                $context['request']->ip(),
                $context['reason'] ?? $message,
            ));
        }
    }
}
