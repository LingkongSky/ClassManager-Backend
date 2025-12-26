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

    // 访问器：简化事件描述
    public function getShortEventAttribute()
    {
        if (strlen($this->event) > 50) {
            return substr($this->event, 0, 50) . '...';
        }
        return $this->event;
    }

    // 查询作用域：按事件类型筛选
    public function scopeByEvent($query, $event)
    {
        return $query->where('event', 'like', "%{$event}%");
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