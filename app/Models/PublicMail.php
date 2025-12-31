<?php
// app/Models/PublicMail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicMail extends Model
{
    use HasFactory;

    protected $table = 'public_mails';
    public $timestamps = false;

    protected $fillable = [
        'mail_id',
        'title',
        'owner',
        'content',
        'target',
        'readtimes',
        'type',
        'start_time',
        'stop_time',
        'status'
    ];

    // 主键不是id
    protected $primaryKey = 'mail_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'id' => 'integer',
        'readtimes' => 'integer',
        'type' => 'integer',
        'status' => 'integer',
        'start_time' => 'datetime',
        'stop_time' => 'datetime',
    ];



    // 访问器：是否有效
    public function getIsValidAttribute()
    {
        $now = now();
        return $this->status == 1 &&
            $this->start_time <= $now &&
            $this->stop_time >= $now;
    }

    // 增加阅读次数
    public function incrementReadTimes()
    {
        $this->readtimes++;
        $this->save();
    }

    // 查询作用域：有效的邮件
    public function scopeValid($query)
    {
        $now = now();
        return $query->where('status', 1)
            ->where('start_time', '<=', $now)
            ->where('stop_time', '>=', $now);
    }

    // 关系：邮件属于一个所有者（学生）
    public function ownerStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'owner', 'account');
    }
}
