<?php
// app/Models/Activity.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';
    public $timestamps = false;

    protected $fillable = [
        'activity_id',
        'name',
        'title',
        'start_time',
        'stop_time',
        'class_id',
        'rename_format',
        'requestment',
        'accept',
        'status',
        'type'
    ];

    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
        'type' => 'integer',
    ];


    protected $primaryKey = 'activity_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // 访问器：活动状态
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? '开启' : '关闭';
    }


    // 访问器：是否在进行中
    public function getIsActiveAttribute()
    {
        $now = time();
        $start = strtotime($this->start_time);
        $stop = strtotime($this->stop_time);
        
        return $now >= $start && $now <= $stop;
    }

    // 查询作用域：开启的活动
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // 查询作用域：按班级筛选
    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    // 查询作用域：进行中的活动
    public function scopeInProgress($query)
    {
        $now = date('Y-m-d H:i:s');
        return $query->where('start_time', '<=', $now)
                    ->where('stop_time', '>=', $now)
                    ->where('status', 1);
    }

    // 关系：活动属于一个班级
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id', 'class_id');
    }

    // 关系：活动有多个作业提交
    public function homeworks(): HasMany
    {
        return $this->hasMany(Homework::class, 'activity_id', 'activity_id');
    }

    // 关系：活动的日志
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'activity_id', 'activity_id');
    }

    // 获取接受的文件类型数组
    public function getAcceptTypes()
    {
        return explode(',', $this->accept);
    }
}