<?php

namespace Metalinked\LaravelDefender\Events;

use Illuminate\Http\Request;

class IpBlocked {
    public function __construct(
        public readonly string $ip,
        public readonly string $reason,
        public readonly Request $request,
    ) {}
}
