<?php

namespace Metalinked\LaravelDefender\Console\Commands;

use Illuminate\Console\Command;
use Metalinked\LaravelDefender\Models\IpLog;

class ShowIpLogs extends Command {
    protected $signature = 'defender:ip-logs {--suspicious : Only show suspicious logs} {--ip= : Filter by IP} {--limit=50 : Number of logs to show}';
    protected $description = 'Show recent IP logs and alerts from Laravel Defender';

    public function handle() {
        $query = IpLog::query()->latest();

        if ($this->option('suspicious')) {
            $query->where('is_suspicious', true);
        }
        if ($ip = $this->option('ip')) {
            $query->where('ip', $ip);
        }

        $logs = $query->limit((int)$this->option('limit'))->get([
            'created_at', 'ip', 'route', 'method', 'user_id', 'is_suspicious', 'reason'
        ]);

        if ($logs->isEmpty()) {
            $this->info(__('defender.logs_no_results'));
            return;
        }

        $this->info(__('defender.logs_header'));
        $this->table(
            [
                __('defender.logs_date', [], 'en') ?? 'Date',
                __('defender.logs_ip', [], 'en') ?? 'IP',
                __('defender.logs_route', [], 'en') ?? 'Route',
                __('defender.logs_method', [], 'en') ?? 'Method',
                __('defender.logs_user', [], 'en') ?? 'User',
                __('defender.logs_suspicious', [], 'en') ?? 'Suspicious',
                __('defender.logs_reason', [], 'en') ?? 'Reason',
            ],
            $logs->map(function ($log) {
                return [
                    $log->created_at,
                    $log->ip,
                    $log->route,
                    $log->method,
                    $log->user_id ?? '-',
                    $log->is_suspicious ? 'Yes' : 'No',
                    $log->reason ?? '-',
                ];
            })->toArray()
        );
        $this->info(__('defender.logs_total', ['count' => $logs->count()]));
    }
}