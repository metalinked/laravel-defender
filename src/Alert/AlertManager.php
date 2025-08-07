<?php
namespace Metalinked\LaravelDefender\Alert;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlertManager {
    public static function send(string $message, array $context = []) {
        $channels = config('defender.alerts.channels', ['log']);

        foreach ($channels as $channel) {
            match ($channel) {
                'log' => self::toLog($message, $context),
                'mail' => self::toMail($message, $context),
                'slack' => self::toSlack($message, $context),
                'webhook' => self::toWebhook($message, $context),
                default => null,
            };
        }
    }

    protected static function toLog(string $message, array $context = []) {
        Log::channel('single')->warning('[Defender] ' . $message, $context);
    }

    protected static function toMail(string $message, array $context = []) {
        $to = config('defender.alerts.mail.to');
        if ($to) {
            Mail::raw('[Defender] ' . $message . "\n\n" . json_encode($context, JSON_PRETTY_PRINT), function ($mail) use ($to) {
                $mail->to($to)->subject('[Defender] Security Alert');
            });
        }
    }

    protected static function toSlack(string $message, array $context = []) {
        $webhook = config('defender.alerts.slack.webhook_url');
        if ($webhook) {
            Http::post($webhook, [
                'text' => '[Defender] ' . $message . "\n" . json_encode($context, JSON_PRETTY_PRINT),
            ]);
        }
    }

    protected static function toWebhook(string $message, array $context = []) {
        $url = config('defender.alerts.webhook.url');
        if ($url) {
            Http::post($url, [
                'message' => $message,
                'context' => $context,
            ]);
        }
    }
}
