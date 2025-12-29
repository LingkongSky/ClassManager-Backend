<?php
// app/Models/Homework.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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


    // 按活动筛选
    public static function getTableByActivity($activityId, $queryWord, $queryCount, $limit = 20)
    {
        $query = Student::select(
            'students.name',
            'students.account',
            'students.class_id',
            'homework.update_time',
            'homework.activity_id'
        )
            ->leftJoin('homework', function ($join) use ($activityId) {
                $join->on('students.account', '=', 'homework.account')
                    ->where('homework.activity_id', '=', $activityId);
            })
            ->where('students.class_id', Activity::getClass($activityId));

        if (!empty($queryWord)) {
            $query->where('students.name', 'like', '%' . $queryWord . '%');
        }

        // 执行查询
        return $query->orderBy('homework.activity_id')
            ->orderBy('students.account')
            ->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    public static function getTableByActivityWithSort(
        $activityId,
        $query_word,
        $query_count,
        $sort_mode,
        $sort_field,
        $limit = 20
    ) {
        $query = Student::select(
            'students.name',
            'students.account',
            'students.class_id',
            'homework.update_time',
            'homework.activity_id'
        )
            ->leftJoin('homework', function ($join) use ($activityId) {
                $join->on('students.account', '=', 'homework.account')
                    ->where('homework.activity_id', '=', $activityId);
            })
            ->where('students.class_id', Activity::getClass($activityId));

        // 添加姓名筛选条件
        if (!empty($query_word)) {
            $query->where('students.name', 'like', '%' . $query_word . '%');
        }
  
        $sort_mode = strtolower($sort_mode) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sort_field, $sort_mode);

        // 执行查询
        return $query->offset($query_count)
            ->limit($limit)
            ->get();
    }



    // 判断学生是否提交某作业
    public static function hasSubmit($account,$activity_id){
        return self::query()
        ->where('activity_id',$activity_id)
        ->where('account',$account)
        ->exists();
    }
    
}
