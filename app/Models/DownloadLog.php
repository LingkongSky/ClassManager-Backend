<?php
// app/Models/DownloadLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    use HasFactory;

    protected $table = 'download_logs';
    public $timestamps = false;

    protected $fillable = [
        'account',
        'file_name',
        'time',
        'type',
        'status',
        'ip'
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public static function log($account, $fileName, $type, $status, $ip)
    {
        return self::create([
            'account' => $account,
            'file_name' => $fileName,
            'type' => $type,
            'status' => $status,
            'ip' => $ip,
            'time' => now(),
        ]);
    }
}
