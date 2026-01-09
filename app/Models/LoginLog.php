<?php
// app/Models/LoginLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    use HasFactory;

    protected $table = 'login_logs';
    public $timestamps = false;

    protected $fillable = [
        'account',
        'time',
        'ip'
    ];

    public static function log($account, $ip)
    {
        return self::create([
            'account' => $account,
            'ip' => $ip,
            'time' => now(),
        ]);
    }
}
