<?php
// app/Models/ActivityLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';
    public $timestamps = false;

    protected $fillable = [
        'account',
        'activity_id',
        'time',
        'event',
        'ip'
    ];

    protected $casts = [
        'id' => 'integer',
        'time' => 'datetime',
    ];

    // 插入数据
    public static function log($account, $activityId, $event, $ip)
    {
        return self::create([
            'account' => $account,
            'activity_id' => $activityId,
            'event' => $event,
            'ip' => $ip,
            'time' => now(),
        ]);
    }
}
