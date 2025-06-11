<?php

namespace Metalinked\LaravelDefender\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        // Log the alert
        Log::warning("[Defender] $subject: $message", $context);

        // Optionally, send an email (if configured)
        $to = config('defender.alert_email');
        if ($to) {
            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)
                     ->subject($subject);
            });
        }

        // Here you could add more channels (Slack, Telegram, etc.) in the future
    }
}
