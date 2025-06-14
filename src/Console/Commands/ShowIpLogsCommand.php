<?php

namespace Metalinked\LaravelDefender\Console\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Console\Command;
use Metalinked\LaravelDefender\Models\IpLog;

class ShowIpLogsCommand extends Command {
    protected $signature = 'defender:ip-logs {--suspicious : Only show suspicious logs} {--ip= : Filter by IP} {--limit=50 : Number of logs to show}';
    protected $description = 'Show recent IP logs and alerts from Laravel Defender';

    public function handle() {
        $table = (new \Metalinked\LaravelDefender\Models\IpLog)->getTable();
        if (!config('defender.ip_logging.enabled', true)) {
            $this->warn(__('defender::defender.db_logging_disabled'));
            return;
        }
        if (!Schema::hasTable($table)) {
            $this->warn(__('defender::defender.logs_table_missing'));
            return;
        }
        
        $query = IpLog::query()->latest();

        if ($this->option('suspicious')) {
            $query->where('is_suspicious', true);
        }
        if ($ip = $this->option('ip')) {
            $query->where('ip', $ip);
        }

        $logs = $query->limit((int)$this->option('limit'))->get([
            'created_at', 'ip', 'route', 'method', 'user_id', 'is_suspicious', 'reason',
            'user_agent', 'referer', 'country_code', 'headers_hash'
        ]);

        if ($logs->isEmpty()) {
            $this->info(__('defender::defender.logs_no_results'));
            return;
        }

        $this->info(__('defender::defender.logs_header'));
        $this->table(
            [
                __('defender::defender.logs_date', [], 'en') ?? 'Date',
                __('defender::defender.logs_ip', [], 'en') ?? 'IP',
                __('defender::defender.logs_route', [], 'en') ?? 'Route',
                __('defender::defender.logs_method', [], 'en') ?? 'Method',
                __('defender::defender.logs_user', [], 'en') ?? 'User',
                __('defender::defender.logs_suspicious', [], 'en') ?? 'Suspicious',
                __('defender::defender.logs_reason', [], 'en') ?? 'Reason',
                'User-Agent', 'Referer', 'Country', 'Headers Hash'
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
                    $log->user_agent ?? '-',
                    $log->referer ?? '-',
                    $log->country_code ?? '-',
                    $log->headers_hash ?? '-',
                ];
            })->toArray()
        );
        $this->info(__('defender::defender.logs_total', ['count' => $logs->count()]));
    }
}
