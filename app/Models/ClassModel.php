<?php
// app/Models/ClassModel.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    public $timestamps = false;

    protected $fillable = [
        'class_id',
        'class_name',
        'student_count'
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    protected $primaryKey = 'class_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // 创建班级
    public static function addClass(string $classId, string $className)
    {
        try {

            // 检查班级ID是否已存在
            if (self::where('class_id', $classId)->exists()) {
                return [
                    'success' => false,
                    'message' => '班级ID已存在'
                ];
            }

            // 创建班级
            $class = self::create([
                'class_id' => $classId,
                'class_name' => $className
            ]);

            return $class;
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
            // 查找班级
            $class = self::where('class_id', $classId)->first();

            if (!$class) {
                return [
                    'success' => false,
                    'message' => '班级不存在'
                ];
            }

            // 删除班级
            $deleted = $class->delete();

            if ($deleted) {
                return [
                    'success' => true,
                    'message' => '班级删除成功'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '班级删除失败'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '删除班级时发生错误: ' . $e->getMessage()
            ];
        }
    }

    // 获取班级名称
    public static function getClassName($classId)
    {
        return self::query()->where('class_id', $classId)->first()->class_name;
    }

    // 查询班级列表
    public static function queryClasses($queryCount = 0, $queryWord = '', $limit = 20)
    {
        $query = self::query();

        if (!empty($queryWord)) {
            $query->where('class_name', 'like', '%' . $queryWord . '%');
        }

        return $query->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    // 查询班级列表2
    public static function queryClassesWithSort(
        $queryCount = 0,
        $queryWord = '',
        $sortMode = 'asc',
        $sortField = 'class_id',
        $limit = 20
    ) {
        $query = self::query();

        if (!empty($queryWord)) {
            $query->where('class_name', 'like', '%' . $queryWord . '%');
        }

        $validFields = ['class_id', 'class_name', 'student_count'];
        if (!in_array($sortField, $validFields)) {
            $sortField = 'class_id';
        }

        $sortMode = strtolower($sortMode);
        if (!in_array($sortMode, ['asc', 'desc'])) {
            $sortMode = 'asc';
        }

        $query->orderBy($sortField, $sortMode);

        return $query->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    // 获取所有班级
    public static function getAllClasses()
    {
        return self::orderBy('class_id')->get(['class_id', 'class_name']);
    }

    // 修改班级信息
    public static function editClass(string $classId, string $newClassName): array
    {
        try {
            // 查找班级
            $class = self::where('class_id', $classId)->first();

            if (!$class) {
                return [
                    'success' => false,
                    'message' => '班级不存在'
                ];
            }

            $class->class_name = $newClassName;
            $class->save();

            return [
                'success' => true,
                'message' => '班级修改成功',
                'class' => $class
            ];
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
        $query = self::query();

        if (!empty($queryWord)) {
            $query->where('class_name', 'like', '%' . $queryWord . '%');
        }

        return $query->count();
    }

    // 更新班级的学生数量
    public static function updateClassStatus()
    {

        $lastRunKey = 'update_class_status_last_run';
        $lastRun = cache()->get($lastRunKey);
        if ($lastRun && (time() - $lastRun) < 60) {
            return;
        }

        $classes = self::all();

        foreach ($classes as $class) {
            $class->student_count = Student::query()
                ->where('class_id', $class->class_id)
                ->count();
            $class->save();
        }
    }
}