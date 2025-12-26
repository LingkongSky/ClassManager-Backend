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

    protected $casts = [
        'id' => 'integer',
        'time' => 'datetime',
    ];

    // 访问器：格式化时间
    public function getFormattedTimeAttribute()
    {
        return $this->time->format('Y-m-d H:i:s');
    }

    // 访问器：获取登录日期
    public function getLoginDateAttribute()
    {
        return $this->time->format('Y-m-d');
    }

    // 查询作用域：最近的登录
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('time', '>=', now()->subDays($days));
    }

    // 查询作用域：按用户筛选
    public function scopeByUser($query, $account)
    {
        return $query->where('account', $account);
    }

    // 关系：登录日志属于一个学生
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'account', 'account');
    }
}