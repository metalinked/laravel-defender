<?php

namespace Metalinked\LaravelDefender\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Metalinked\LaravelDefender\Models\IpLog;

class StatsCommand extends Command
{
    protected $signature = 'defender:stats';
    protected $description = 'Show statistics about Defender IP logs and suspicious activity';

    public function handle()
    {
        $table = (new IpLog)->getTable();
        if (! config('defender.ip_logging.enabled', true)) {
            $this->warn(__('defender::defender.db_logging_disabled'));

            return;
        }
        if (! Schema::hasTable($table)) {
            $this->warn(__('defender::defender.logs_table_missing'));

            return;
        }

        $total = IpLog::count();
        $uniqueIps = IpLog::distinct('ip')->count('ip');
        $suspicious = IpLog::where('is_suspicious', true)->count();

        $topIps = IpLog::select('ip', DB::raw('count(*) as total'))
            ->groupBy('ip')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topCountries = IpLog::select('country_code', DB::raw('count(*) as total'))
            ->whereNotNull('country_code')
            ->groupBy('country_code')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topRoutes = IpLog::select('route', DB::raw('count(*) as total'))
            ->groupBy('route')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $this->info(__('defender::defender.stats_title'));
        $this->line(__('defender::defender.stats_separator'));
        $this->line(__('defender::defender.stats_total_logs', ['count' => $total]));
        $this->line(__('defender::defender.stats_unique_ips', ['count' => $uniqueIps]));
        $this->line(__('defender::defender.stats_suspicious', ['count' => $suspicious]));

        $this->line('');
        $this->info(__('defender::defender.stats_top_ips'));
        $this->table(
            [__('defender::defender.stats_ip'), __('defender::defender.stats_attempts')],
            $topIps->map(fn ($r) => [$r->ip, $r->total])->toArray()
        );

        $this->info(__('defender::defender.stats_top_countries'));
        $this->table(
            [__('defender::defender.stats_country'), __('defender::defender.stats_attempts')],
            $topCountries->map(fn ($r) => [$r->country_code, $r->total])->toArray()
        );

        $this->info(__('defender::defender.stats_top_routes'));
        $this->table(
            [__('defender::defender.stats_route'), __('defender::defender.stats_attempts')],
            $topRoutes->map(fn ($r) => [$r->route, $r->total])->toArray()
        );
    }
}
