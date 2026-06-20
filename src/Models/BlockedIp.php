<?php

namespace Metalinked\LaravelDefender\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model {
    protected $table = 'defender_blocked_ips';

    protected $fillable = ['ip', 'reason', 'blocked_until'];

    protected $casts = ['blocked_until' => 'datetime'];
}
