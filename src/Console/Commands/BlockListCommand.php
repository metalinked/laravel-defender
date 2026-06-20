<?php

namespace Metalinked\LaravelDefender\Console\Commands;

use Illuminate\Console\Command;
use Metalinked\LaravelDefender\Services\BlocklistService;

class BlockListCommand extends Command {
    protected $signature = 'defender:block-list';

    protected $description = 'List all currently blocked IP addresses';

    public function handle(): void {
        $blocked = BlocklistService::all();

        if ($blocked->isEmpty()) {
            $this->info(__('defender::defender.block_list_empty'));
            return;
        }

        $this->info(__('defender::defender.block_list_header'));
        $this->table(
            [
                __('defender::defender.block_list_ip'),
                __('defender::defender.block_list_reason'),
                __('defender::defender.block_list_until'),
                __('defender::defender.logs_date'),
            ],
            $blocked->map(fn ($row) => [
                $row->ip,
                $row->reason ?? '-',
                $row->blocked_until?->toDateTimeString() ?? __('defender::defender.block_list_permanent'),
                $row->created_at->toDateTimeString(),
            ])->toArray()
        );

        $this->line(__('defender::defender.block_list_total', ['count' => $blocked->count()]));
    }
}
