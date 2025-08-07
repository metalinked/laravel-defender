<?php

namespace Metalinked\LaravelDefender\Models;

use Illuminate\Database\Eloquent\Model;

class IpLog extends Model {
    protected $table = 'defender_ip_logs';
    
    protected $fillable = [
        'ip',
        'user_id',
        'route',
        'method',
        'user_agent',
        'referer',
        'country_code',
        'headers_hash',
        'is_suspicious',
        'reason',
    ];
}
