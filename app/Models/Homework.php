<?php
// app/Models/Homework.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Homework extends Model
{
    use HasFactory;

    protected $table = 'homework';
    public $timestamps = false;

    protected $fillable = [
        'account',
        'file_name',
        'class_id',
        'activity_id',
        'update_time'
    ];

    protected $casts = [
        'id' => 'integer',
        'update_time' => 'datetime',
    ];

    // 访问器：获取文件扩展名
    public function getFileExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    // 访问器：获取文件名（不含扩展名）
    public function getFileNameWithoutExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_FILENAME);
    }

    // 访问器：格式化更新时间
    public function getFormattedUpdateTimeAttribute()
    {
        return date('Y-m-d H:i:s', strtotime($this->update_time));
    }

    // 查询作用域：按学生筛选
    public function scopeByStudent($query, $account)
    {
        return $query->where('account', $account);
    }

    // 查询作用域：按活动筛选
    public function scopeByActivity($query, $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    // 查询作用域：最新提交
    public function scopeLatestSubmission($query)
    {
        return $query->orderBy('update_time', 'desc');
    }

    // 关系：作业属于一个学生
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'account', 'account');
    }

    // 关系：作业属于一个活动
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id', 'activity_id');
    }

    // 关系：作业属于一个班级
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id', 'class_id');
    }
}