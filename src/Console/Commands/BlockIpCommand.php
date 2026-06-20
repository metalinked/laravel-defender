<?php

namespace Metalinked\LaravelDefender\Console\Commands;

use Illuminate\Console\Command;
use Metalinked\LaravelDefender\Services\BlocklistService;

class BlockIpCommand extends Command {
    protected $signature = 'defender:block-ip
                            {ip : The IP address to block}
                            {--reason= : Reason for blocking}
                            {--hours= : Block for N hours (omit for permanent)}';

    protected $description = 'Block an IP address from accessing the application';

    public function handle(): void {
        $ip = $this->argument('ip');

        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->error(__('defender::defender.block_ip_invalid', ['ip' => $ip]));
            return;
        }

        $reason = $this->option('reason') ?? 'Manually blocked';
        $hours = $this->option('hours');
        $until = $hours ? now()->addHours((int) $hours) : null;

        BlocklistService::block($ip, $reason, $until);

        if ($until) {
            $this->info(__('defender::defender.block_ip_until', ['ip' => $ip, 'until' => $until->toDateTimeString()]));
        } else {
            $this->info(__('defender::defender.block_ip_permanent', ['ip' => $ip]));
        }
    }
}
