<?php

namespace Metalinked\LaravelDefender\Console\Commands;

use Illuminate\Console\Command;
use Metalinked\LaravelDefender\Saas\SaasClient;

class DefenderCheckSaas extends Command {
    protected $signature = 'defender:check-saas';
    protected $description = 'Check SaaS connection and token validity';

    public function handle() {
        $client = new SaasClient();
        $result = $client->ping();

        if ($result['success']) {
            $this->info('SaaS connection successful!');
            $this->line(print_r($result['data'], true));
        } else {
            $this->error('SaaS connection failed: ' . $result['message']);
        }
    }
}
