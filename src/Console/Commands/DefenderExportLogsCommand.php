<?php

namespace Metalinked\LaravelDefender\Console\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Console\Command;
use Metalinked\LaravelDefender\Models\IpLog;
use Illuminate\Support\Facades\File;

class DefenderExportLogsCommand extends Command {
    protected $signature = 'defender:export-logs
        {--ip= : Filter by IP}
        {--from= : Start date (Y-m-d)}
        {--to= : End date (Y-m-d)}
        {--suspicious : Only suspicious logs}
        {--format=csv : Export format: csv or json}
        {--output= : Output file path (default: defender-logs.csv or .json)}';

    protected $description = 'Export Defender IP logs to CSV or JSON';

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
        
        $query = IpLog::query();

        if ($ip = $this->option('ip')) {
            $query->where('ip', $ip);
        }
        if ($this->option('suspicious')) {
            $query->where('is_suspicious', true);
        }
        if ($from = $this->option('from')) {
            $query->where('created_at', '>=', $from);
        }
        if ($to = $this->option('to')) {
            $query->where('created_at', '<=', $to);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $format = strtolower($this->option('format') ?? 'csv');
        $output = $this->option('output') ?? ('defender-logs.' . $format);

        if ($format === 'json') {
            File::put($output, $logs->toJson(JSON_PRETTY_PRINT));
            $this->info(__('defender::defender.export_logs_json', [
                'count' => $logs->count(),
                'output' => $output,
            ]));
        } else {
            // CSV export
            $csv = $this->toCsv($logs);
            File::put($output, $csv);
            $this->info(__('defender::defender.export_logs_csv', [
                'count' => $logs->count(),
                'output' => $output,
            ]));
        }
    }

    protected function toCsv($logs) {
        if ($logs->isEmpty()) return '';
        $headers = array_keys($logs->first()->toArray());
        $rows = [$headers];
        foreach ($logs as $log) {
            $rows[] = array_map(function($h) use ($log) {
                return $log[$h];
            }, $headers);
        }
        // Convert to CSV string
        $csv = '';
        foreach ($rows as $row) {
            $csv .= '"' . implode('","', array_map(fn($v) => str_replace('"', '""', $v), $row)) . '"' . "\n";
        }
        return $csv;
    }
}