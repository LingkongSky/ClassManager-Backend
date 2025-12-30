<?php
// app/Models/MailLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailLog extends Model
{
    use HasFactory;

    protected $table = 'mail_logs';
    public $timestamps = false;

    protected $fillable = [
        'account',
        'mail_id',
        'time',
        'event',
        'ip'
    ];

    protected $casts = [
        'id' => 'integer',
        'time' => 'datetime',
    ];

}