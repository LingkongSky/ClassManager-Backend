<?php
// app/Models/HomeworkLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'ip'
    ];

    protected $casts = [
        'id' => 'integer',
        'update_time' => 'datetime',
    ];

    // 访问器：格式化IP地址
    public function getFormattedIpAttribute()
    {
        return long2ip($this->ip) ?: $this->ip;
    }

    // 查询作用域：按时间段筛选
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('update_time', [$start, $end]);
    }

    // 关系：日志属于一个学生
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'account', 'account');
    }

    // 关系：日志属于一个活动
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id', 'activity_id');
    }
}