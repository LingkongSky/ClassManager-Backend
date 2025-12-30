<?php
// app/Models/Activity.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityLog;

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

        $activityId = time() + rand(0, 9999);

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

        $activity = new self();
        $activity->fill($data);
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

        $data = [
            'name' => $name,
            'title' => $title,
            'class_id' => $classId,
            'requestment' => $requestment,
            'rename_format' => $renameFormat,
            'accept' => strtolower($accept),
            'start_time' => $startTime,
            'stop_time' => $stopTime,
        ];

        return $activity->update($data);
    }

    // 编辑活动时间
    public static function editTime(
        string $activityId,
        string $startTime,
        string $stopTime,
    ): bool {
        $activity = self::where('activity_id', $activityId)->first();

        if (!$activity) {
            return false;
        }

        $data = [
            'start_time' => $startTime,
            'stop_time' => $stopTime,
        ];

        return $activity->update($data);
    }

    public static function getClass($activityId)
    {
        return self::query()
            ->where('activity_id', $activityId)
            ->first()
            ->class_id;
    }

    // TODO 鉴权
    public static function getActivityProgress($activityId)
    {
        $activity = self::query()->where('activity_id', $activityId)->first();
        $allCount = Student::query()->where('class_id', $activity->class_id)->count();
        $sumbitCount = Homework::query()->where('activity_id', $activityId)->count();
        return $sumbitCount . "/" . $allCount;
    }


    // TODO 鉴权
    // 查询活动表格
    public static function getTableByClass($classId, $queryWord, $queryCount, $limit = 20)
    {
        return self::query()->where('class_id', $classId)
            ->where('name', 'like', '%' . $queryWord . '%')
            ->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    // 根据班级获取列表
    public static function getListByClass($classId)
    {
        $query = self::query()
            ->where('class_id', $classId)
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

        // 按照状态分组
        foreach ($query as $activity) {
            $activityData = [
                'value' => $activity->activity_id,
                'title' => $activity->name,
            ];

            $status = (string)$activity->status; // 转换为字符串以匹配比较

            // 根据状态放入对应的分组
            if ($status === "0") {
                $groupedActivities[0]['children'][] = $activityData;
            } elseif ($status === "1") {
                $groupedActivities[1]['children'][] = $activityData;
            } elseif ($status === "2") {
                $groupedActivities[2]['children'][] = $activityData;
            }
        }

        return $groupedActivities;
    }

    // 获取可接受文件类型
    public static function getAccept($activityId): string
    {
        return self::query()
            ->where('activity_id', $activityId)
            ->first()
            ->accept;
    }

    // 获取单活动信息用于填充表单
    public static function getInfo($activityId)
    {
        return self::query()
            ->where('activity_id', $activityId)
            ->select('activity_id', 'name', 'title', 'start_time', 'stop_time', 'accept', 'class_id', 'rename_format', 'requestment', 'status')
            ->first();
    }

    // 根据班级获取活动数量
    public static function getCountByClass(string $classId, ?string $queryWord = '')
    {
        $query = self::query()->where('class_id', $classId);
        if (!empty($queryWord)) {
            $query->where('name', 'like', '%' . $queryWord . '%');
        }

        return $query->count();
    }

    // 根据班级获取正在进行的活动数量
    public static function getTodosCountByClass(string $classId)
    {
        $query = self::query()
            ->where('class_id', $classId)
            ->where('status', 1);
        return $query->count();
    }

    // 根据班级获取正在进行的活动数量
    public static function getStartedTodosCountByClass(string $classId)
    {
        $query = self::query()
            ->where('class_id', $classId)
            ->where('status', 1)
            ->orWhere('status', 2);
        return $query->count();
    }

    // 更新活动
    public static function updateActivityStatus()
    {

        $lastRunKey = 'update_activity_status_last_run';
        $lastRun = cache()->get($lastRunKey);
        if ($lastRun && (time() - $lastRun) < 30) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        self::where('start_time', '<=', $now)
            ->where('stop_time', '>=', $now)
            ->where('status', '!=', 1)
            ->update(['status' => 1]);

        self::where('stop_time', '<', $now)
            ->where('status', '!=', 2)
            ->update(['status' => 2]);

        self::where('start_time', '>', $now)
            ->where('status', '!=', 0)
            ->update(['status' => 0]);
    }
}
