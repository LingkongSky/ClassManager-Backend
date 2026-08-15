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
            self::create([
                'account' => $account,
                'file_name' => $fileName,
                'class_id' => $classId,
                'activity_id' => $activityId,
                'update_time' => $now
            ]);
        }
    }


    // 按活动筛选
    public static function getTableByActivity($activityId, $queryWord, $queryCount, $limit = 20)
    {
        $query = Student::select(
            'student.name',
            'student.account',
            'scm.class_id',
            'homework.update_time',
            'homework.activity_id'
        )
            ->join('student_class_map as scm', 'scm.student_id', '=', 'student.id')
            ->leftJoin('homework', function ($join) use ($activityId) {
                $join->on('student.account', '=', 'homework.account')
                    ->where('homework.activity_id', '=', $activityId);
            })
            ->where('scm.class_id', Activity::getClass($activityId));

        if (!empty($queryWord)) {
            $query->where('student.name', 'like', '%' . $queryWord . '%');
        }

        // 执行查询
        return $query->orderBy('homework.activity_id')
            ->orderBy('student.account')
            ->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    // 获取筛选后的活动表格
    public static function getTableByActivityWithSort(
        $activityId,
        $queryWord,
        $queryCount,
        $sortMode,
        $sortField,
        $limit = 20
    ) {
        $query = Student::select(
            'student.name',
            'student.account',
            'scm.class_id',
            'homework.update_time',
            'homework.activity_id'
        )
            ->join('student_class_map as scm', 'scm.student_id', '=', 'student.id')
            ->leftJoin('homework', function ($join) use ($activityId) {
                $join->on('student.account', '=', 'homework.account')
                    ->where('homework.activity_id', '=', $activityId);
            })
            ->where('scm.class_id', Activity::getClass($activityId));

        // 添加姓名筛选条件
        if (!empty($queryWord)) {
            $query->where('student.name', 'like', '%' . $queryWord . '%');
        }

        $sortMode = strtolower($sortMode) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortMode);

        // 执行查询
        return $query->offset($queryCount)
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
    public static function hasSubmit($account, $activityId)
    {
        return self::query()
            ->where('activity_id', $activityId)
            ->where('account', $account)
            ->exists();
    }

    // 删除学生的提交记录
    public static function deleteSubmit($account, $activityId)
    {
        return self::query()
            ->where('activity_id', $activityId)
            ->where('account', $account)
            ->delete();
    }
}
