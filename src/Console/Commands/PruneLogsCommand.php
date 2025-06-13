<?php

namespace Metalinked\LaravelDefender\Console\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class PruneLogsCommand extends Command
{
    protected $signature = 'defender:prune-logs 
                            {--days=90 : Delete logs older than this number of days}
                            {--laravel : Also prune Laravel log files}';

    protected $description = 'Prune old Defender logs from the database and optionally Laravel log files';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        // 1. Prune database logs
        $table = (new \Metalinked\LaravelDefender\Models\IpLog)->getTable();
        if (Schema::hasTable($table)) {
            $count = DB::table($table)
                ->where('created_at', '<', $cutoff)
                ->count();

            if ($count > 0) {
                DB::table($table)
                    ->where('created_at', '<', $cutoff)
                    ->delete();
                $this->info(__('defender::defender.prune_deleted', ['count' => $count, 'days' => $days]));
            } else {
                $this->info(__('defender::defender.prune_none', ['days' => $days]));
            }
        } else {
            $this->warn(__('defender::defender.prune_table_missing', ['table' => $table]));
        }

        // 2. Prune Laravel log files (optional)
        if ($this->option('laravel')) {
            $logPath = storage_path('logs');
            $deleted = 0;
            foreach (File::files($logPath) as $file) {
                if ($file->getExtension() === 'log' && $file->getMTime() < $cutoff->getTimestamp()) {
                    File::delete($file->getRealPath());
                    $deleted++;
                }
            }
            $this->info(__('defender::defender.prune_laravel_deleted', ['count' => $deleted, 'days' => $days]));
        }
    }
}