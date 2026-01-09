<?php
// app/Models/Activity.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activity';
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
        'status' => 'integer',
        'type' => 'integer',
    ];

    protected $primaryKey = 'activity_id';
    public $incrementing = false;
    protected $keyType = 'string';


    // 新增活动
    public static function create(
        string $name,
        string $title,
        string $classId,
        string $requestment,
        string $renameFormat,
        string $accept,
        string $startTime,
        string $stopTime,
    ) {
        $activityId = time() . mt_rand(1000, 9999);

        $data = [
            'activity_id' => $activityId,
            'name' => $name,
            'title' => $title,
            'class_id' => $classId,
            'requestment' => $requestment,
            'rename_format' => $renameFormat,
            'accept' => strtolower($accept),
            'start_time' => $startTime,
            'stop_time' => $stopTime,
            'status' => 0,
            'type' => 0,
        ];

        $activity = new self($data);
        $activity->save();

        return $activityId;
    }

    // 编辑活动
    public static function edit(
        string $activityId,
        string $name,
        string $title,
        string $classId,
        string $requestment,
        string $renameFormat,
        string $accept,
        string $startTime,
        string $stopTime,
    ): bool {
        $activity = self::where('activity_id', $activityId)->first();

        if (!$activity) {
            return false;
        }

        return $activity->update([
            'name' => $name,
            'title' => $title,
            'class_id' => $classId,
            'requestment' => $requestment,
            'rename_format' => $renameFormat,
            'accept' => strtolower($accept),
            'start_time' => $startTime,
            'stop_time' => $stopTime,
        ]);
    }

    // 编辑活动时间
    public static function editTime(
        string $activityId,
        string $startTime,
        string $stopTime,
    ): bool {
        return self::where('activity_id', $activityId)
            ->update([
                'start_time' => $startTime,
                'stop_time' => $stopTime,
            ]) > 0;
    }

    public static function getClass($activityId)
    {
        return self::where('activity_id', $activityId)->value('class_id');
    }

    public static function getActivityProgress($activityId)
    {
        // 一次性获取班级ID
        $classId = self::where('activity_id', $activityId)->value('class_id');

        if (!$classId) {
            return "0/0";
        }

        $allCount = Student::where('class_id', $classId)->count();
        $sumbitCount = Homework::where('activity_id', $activityId)->count();

        return $sumbitCount . "/" . $allCount;
    }

    // 查询活动表格
    public static function getTableByClass($classId, $queryWord, $queryCount, $limit = 20)
    {
        $query = self::where('class_id', $classId);

        if (!empty($queryWord)) {
            $query->where('name', 'like', '%' . $queryWord . '%');
        }

        return $query->skip($queryCount)
            ->take($limit)
            ->get();
    }

    // 根据班级获取列表
    public static function getListByClass($classId)
    {
        $activities = self::where('class_id', $classId)
            ->select('activity_id', 'name', 'status')
            ->limit(200)
            ->get();

        // 初始化分组数组
        $groupedActivities = [
            [
                'title' => '未开始',
                'value' => '0',
                "selectable" => false,
                'children' => []
            ],
            [
                'title' => '进行中',
                'value' => '1',
                "selectable" => false,
                'children' => []
            ],
            [
                'title' => '已结束',
                'value' => '2',
                "selectable" => false,
                'children' => []
            ]
        ];

        $childrenRefs = [
            &$groupedActivities[0]['children'],
            &$groupedActivities[1]['children'],
            &$groupedActivities[2]['children']
        ];

        // 按照状态分组
        foreach ($activities as $activity) {
            $status = (int)$activity->status;

            if ($status >= 0 && $status <= 2) {
                $childrenRefs[$status][] = [
                    'value' => $activity->activity_id,
                    'title' => $activity->name,
                ];
            }
        }

        return $groupedActivities;
    }

    // 获取可接受文件类型
    public static function getAccept($activityId): string
    {
        return self::where('activity_id', $activityId)->value('accept') ?? '';
    }

    // 获取单活动信息用于填充表单
    public static function getInfo($activityId)
    {
        return self::where('activity_id', $activityId)
            ->select('activity_id', 'name', 'title', 'start_time', 'stop_time', 'accept', 'class_id', 'rename_format', 'requestment', 'status')
            ->first();
    }

    // 根据班级获取活动数量
    public static function getCountByClass(string $classId, ?string $queryWord = '')
    {
        $query = self::where('class_id', $classId);

        if (!empty($queryWord)) {
            $query->where('name', 'like', '%' . $queryWord . '%');
        }

        return $query->count();
    }

    // 根据班级获取正在进行的活动数量
    public static function getTodosCountByClass(string $classId)
    {
        return self::where('class_id', $classId)
            ->where('status', 1)
            ->count();
    }

    // 根据班级获取正在进行的活动数量
    public static function getStartedTodosCountByClass(string $classId)
    {
        return self::where('class_id', $classId)
            ->whereIn('status', [1, 2])
            ->count();
    }

    // 根据活动ID查询发布文件
    public static function getActivityFiles(string $activityId)
    {
        $disk = Storage::disk('publish');
        $directory = $activityId;

        if (!$disk->exists($directory)) {
            return [];
        }

        $files = $disk->files($directory);

        return array_map(function ($file) {
            return basename($file);
        }, $files);
    }

    // 更新活动状态
    public static function updateActivityStatus()
    {
        $now = date('Y-m-d H:i:s');

        // 更新状态为1（进行中）的记录
        self::where('start_time', '<=', $now)
            ->where('stop_time', '>=', $now)
            ->where('status', '!=', 1)
            ->update(['status' => 1]);

        // 更新状态为2（已结束）的记录
        self::where('stop_time', '<', $now)
            ->where('status', '!=', 2)
            ->update(['status' => 2]);

        // 更新状态为0（未开始）的记录
        self::where('start_time', '>', $now)
            ->where('status', '!=', 0)
            ->update(['status' => 0]);

        return true;
    }
}
