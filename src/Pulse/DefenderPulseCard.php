<?php

namespace Metalinked\LaravelDefender\Pulse;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;
use Metalinked\LaravelDefender\Models\IpLog;

#[Lazy]
class DefenderPulseCard extends Card {
    public function render(): View {
        $table = (new IpLog)->getTable();

        if (! Schema::hasTable($table)) {
            return view('defender::livewire.pulse.defender', ['hasTable' => false]);
        }

        return view('defender::livewire.pulse.defender', [
            'hasTable' => true,
            'totalThreats' => IpLog::where('is_suspicious', true)->count(),
            'recentThreats' => IpLog::where('is_suspicious', true)
                ->where('created_at', '>=', now()->subHour())
                ->count(),
            'topIps' => IpLog::where('is_suspicious', true)
                ->select('ip', DB::raw('count(*) as total'))
                ->groupBy('ip')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),
            'latestThreats' => IpLog::where('is_suspicious', true)
                ->latest()
                ->limit(8)
                ->get(['ip', 'route', 'reason', 'country_code', 'created_at']),
        ]);
    }
}
