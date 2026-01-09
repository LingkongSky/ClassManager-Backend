<?php
// app/Models/HomeworkLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkLog extends Model
{
    use HasFactory;

    protected $table = 'homework_logs';
    public $timestamps = false;

    protected $fillable = [
        'account',
        'file_name',
        'class_id',
        'activity_id',
        'update_time',
        'event',
        'ip',
    ];

    public static function log($account, $fileName, $classId, $activityId,  $event, $ip)
    {
        return self::create([
            'account' => $account,
            'file_name' => $fileName,
            'class_id' => $classId,
            'activity_id' => $activityId,
            'event' => $event,
            'ip' => $ip,
            'update_time' => now(),
        ]);
    }
}
