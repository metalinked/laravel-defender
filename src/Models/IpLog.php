<?php

namespace Metalinked\LaravelDefender\Models;

use Illuminate\Database\Eloquent\Model;

class IpLog extends Model {

    protected $table = 'defender_ip_logs';
    
    protected $fillable = [
        'ip', 'route', 'method', 'user_id', 'is_suspicious', 'reason'
    ];
}
