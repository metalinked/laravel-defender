<?php

namespace Metalinked\LaravelDefender\Console\Commands;

use Illuminate\Console\Command;
use Metalinked\LaravelDefender\Services\BlocklistService;

class UnblockIpCommand extends Command {
    protected $signature = 'defender:unblock-ip {ip : The IP address to unblock}';

    protected $description = 'Remove an IP address from the Defender block list';

    public function handle(): void {
        $ip = $this->argument('ip');

        if (BlocklistService::unblock($ip)) {
            $this->info(__('defender::defender.unblock_ip_success', ['ip' => $ip]));
        } else {
            $this->warn(__('defender::defender.unblock_ip_not_found', ['ip' => $ip]));
        }
    }
}
