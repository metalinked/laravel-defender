<?php

namespace Metalinked\LaravelDefender\Events;

use Illuminate\Http\Request;

class SuspiciousRequestDetected {
    public function __construct(
        public readonly Request $request,
        public readonly string $ip,
        public readonly string $reason,
    ) {}
}
