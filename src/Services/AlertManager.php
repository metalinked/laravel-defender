<?php

namespace Metalinked\LaravelDefender\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Metalinked\LaravelDefender\Models\IpLog;
use Metalinked\LaravelDefender\Detection\GeoService;

class AlertManager {
    /**
     * Send a security alert.
     *
     * @param string $subject
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function send(string $subject, string $message, array $context = []) {
        
        $channels = config('defender.alerts.channels', []);

        // Log channel
        if (in_array('log', $channels)) {
            Log::warning("[Defender] $subject: $message", $context);
        }

        // Mail channel
        $to = config('defender.alerts.mail.to');
        if (in_array('mail', $channels) && $to) {
            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)
                     ->subject($subject);
            });
        }

        // Database channel
        if (in_array('database', $channels) && isset($context['request'])) {
            $table = (new \Metalinked\LaravelDefender\Models\IpLog)->getTable();
            if (Schema::hasTable($table)) {
                $request = $context['request'];
                IpLog::create([
                    'ip' => $request->ip(),
                    'route' => $request->path(),
                    'method' => $request->method(),
                    'user_id' => auth()->check() ? auth()->id() : null,
                    'is_suspicious' => true,
                    'reason' => $context['reason'] ?? 'alert',
                    'user_agent' => $request->userAgent(),
                    'referer' => $request->headers->get('referer'),
                    'country_code' => GeoService::getCountryCode($request->ip()),
                    'headers_hash' => hash('sha256', json_encode($request->headers->all())),
                ]);
            }
        }

        // Slack channel
        if (in_array('slack', $channels)) {
            $slackUrl = config('defender.alerts.slack.webhook_url');
            if ($slackUrl) {
                try {
                    \Illuminate\Support\Facades\Http::post($slackUrl, [
                        'text' => "[Defender] $subject\n$message"
                    ]);
                } catch (\Exception $e) {
                    Log::error('Defender Slack alert failed: ' . $e->getMessage());
                }
            }
        }

        // Webhook channel
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

        // Here we could add more channels (Telegram, etc.) in the future
    }
}