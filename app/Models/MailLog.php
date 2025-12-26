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


    // 查询作用域：按邮件筛选
    public function scopeByMail($query, $mailId)
    {
        return $query->where('mail_id', $mailId);
    }

    // 关系：邮件日志属于一个学生
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'account', 'account');
    }

    // 关系：邮件日志属于一封公共邮件
    public function publicMail(): BelongsTo
    {
        return $this->belongsTo(PublicMail::class, 'mail_id', 'mail_id');
    }
}