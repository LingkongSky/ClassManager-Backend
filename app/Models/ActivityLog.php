<?php
// app/Models/ActivityLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
