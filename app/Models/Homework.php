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

    // 提交作业
    public static function submitHomework($activityId, $account, $classId, $fileName)
    {
        $homework = self::where('activity_id', $activityId)
            ->where('account', $account)
            ->first();

        $now = now();

        if ($homework) {
            $homework->update([
                'file_name' => $fileName,
                'update_time' => $now,
            ]);
        } else {
            $homework = self::create([
                'account' => $account,
                'file_name' => $fileName,
                'class_id' => $classId,
                'activity_id' => $activityId,
                'update_time' => $now
            ]);
        }
        return [
            'success' => true,
            'homework' => $homework,
            'message' => '作业提交成功'
        ];
    }


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

    // 获取筛选后的活动表格
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

    // 获取提交信息
    public static function getSubmitInfo($account, $activityId)
    {
        return self::where('account', $account)
                ->where('activity_id', $activityId)
                ->first();
    }

    // 判断学生是否提交某作业
    public static function hasSubmit($account, $activity_id)
    {
        return self::query()
            ->where('activity_id', $activity_id)
            ->where('account', $account)
            ->exists();
    }

    // 删除学生的提交记录
    public static function deleteSubmit($account, $activity_id)
    {
        return self::query()
            ->where('activity_id', $activity_id)
            ->where('account', $account)
            ->delete();
    }
}
