<?php
// app/Models/ClassModel.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'class';

    public $timestamps = false;

    protected $fillable = [
        'class_id',
        'class_name',
        'student_count'
    ];

    protected $primaryKey = 'class_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // 创建班级
    public static function addClass(string $classId, string $className)
    {
        try {
            if (self::where('class_id', $classId)->exists()) {
                return [
                    'success' => false,
                    'message' => '班级ID已存在'
                ];
            }

            return self::create([
                'class_id' => $classId,
                'class_name' => $className,
                'student_count' => 0
            ]);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '班级创建失败: ' . $e->getMessage()
            ];
        }
    }

    // 根据班级ID删除班级
    public static function deleteClass(string $classId)
    {
        try {
            $deleted = self::where('class_id', $classId)->delete();

            if ($deleted > 0) {
                return [
                    'success' => true,
                    'message' => '班级删除成功'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '班级不存在'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '删除班级时发生错误: ' . $e->getMessage()
            ];
        }
    }

    public static function getClassName($classId)
    {
        $cacheKey = 'class_name_' . $classId;
        $cacheTime = 30;

        return Cache::remember($cacheKey, $cacheTime, function () use ($classId) {
            return self::where('class_id', $classId)->value('class_name');
        });
    }

    // 查询班级列表
    public static function queryClasses($queryCount = 0, $queryWord = '', $limit = 20)
    {
        $query = self::query();

        if (!empty($queryWord)) {
            $query->where('class_name', 'like', $queryWord . '%');
        }

        return $query->skip($queryCount)
            ->take($limit)
            ->get(['class_id', 'class_name', 'student_count']);
    }

    // 查询班级列表2
    public static function queryClassesWithSort(
        $queryCount = 0,
        $queryWord = '',
        $sortMode = 'asc',
        $sortField = 'class_id',
        $limit = 20
    ) {
        $validFields = ['class_id', 'class_name', 'student_count'];
        if (!in_array($sortField, $validFields)) {
            $sortField = 'class_id';
        }

        $sortMode = strtolower($sortMode);
        if (!in_array($sortMode, ['asc', 'desc'])) {
            $sortMode = 'asc';
        }

        $query = self::query();

        if (!empty($queryWord)) {
            $query->where('class_name', 'like', $queryWord . '%');
        }

        return $query->orderBy($sortField, $sortMode)
            ->skip($queryCount)
            ->take($limit)
            ->get(['class_id', 'class_name', 'student_count']);
    }

    // 获取所有班级
    public static function getAllClasses()
    {
        $cacheKey = 'all_classes_list';
        $cacheTime = 60;

        return Cache::remember($cacheKey, $cacheTime, function () {
            return self::orderBy('class_id')->get(['class_id', 'class_name']);
        });
    }

    // 修改班级信息
    public static function editClass(string $classId, string $newClassName): array
    {
        try {
            $updated = self::where('class_id', $classId)
                ->update(['class_name' => $newClassName]);

            if ($updated > 0) {
                Cache::forget('class_name_' . $classId);
                Cache::forget('all_classes_list');

                $class = self::where('class_id', $classId)->first();

                return [
                    'success' => true,
                    'message' => '班级修改成功',
                    'class' => $class
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '班级不存在'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '修改班级时发生错误: ' . $e->getMessage()
            ];
        }
    }

    // 获取班级数量
    public static function getClassCount(?string $queryWord = ''): int
    {
        $cacheKey = 'class_count_' . md5($queryWord ?? '');
        $cacheTime = 300;

        return Cache::remember($cacheKey, $cacheTime, function () use ($queryWord) {
            $query = self::query();

            if (!empty($queryWord)) {
                $query->where('class_name', 'like', $queryWord . '%');
            }

            return $query->count();
        });
    }

    // 更新班级的学生数量
    public static function updateClassStatus()
    {
        $lastRunKey = 'update_class_status_last_run';
        $lastRun = Cache::get($lastRunKey);

        if ($lastRun && (time() - $lastRun) < 60) {
            return;
        }

        DB::statement("
            UPDATE class c
            SET student_count = (
                SELECT COUNT(*) 
                FROM student s 
                WHERE s.class_id = c.class_id
            )
        ");

        Cache::put($lastRunKey, time(), 65);

        Cache::forget('all_classes_list');
    }

    public static function updateTargetClass(string $classId): array
    {
        try {
            $updated = DB::update("
                UPDATE class c
                SET student_count = (
                    SELECT COUNT(*) 
                    FROM student s 
                    WHERE s.class_id = ?
                )
                WHERE c.class_id = ?
            ", [$classId, $classId]);

            Cache::forget('all_classes_list');

            return [
                'success' => true,
                'message' => '班级更新成功',
                'updated' => $updated
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '更新班级时发生错误: ' . $e->getMessage()
            ];
        }
    }
}
