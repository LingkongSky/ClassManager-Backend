<?php
// app/Models/DownloadLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'id' => 'integer',
        'time' => 'datetime',
        'status' => 'integer',
    ];


    // 查询作用域：成功的下载
    public function scopeSuccessful($query)
    {
        return $query->where('status', 1);
    }

    // 查询作用域：按文件类型筛选
    public function scopeByFileType($query, $type)
    {
        return $query->where('type', $type);
    }

    // 关系：下载日志属于一个学生
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'account', 'account');
    }
}