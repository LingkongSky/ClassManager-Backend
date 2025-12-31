<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\ExportRequest;
use App\Http\Requests\Activity\GetCountRequest;
use App\Http\Requests\Activity\GetHomeworkTableRequest;
use App\Http\Requests\Activity\GetListRequest;
use App\Http\Requests\Activity\GetTableRequest;
use App\Models\Activity;
use App\Models\ClassModel;
use App\Models\Homework;
use Illuminate\Support\Facades\Storage;
use App\Utils\ResponseTool;

class ActivityListController extends Controller
{
    // 编辑列表:列出活动
    public function getList(GetListRequest $request)
    {
        $data = Activity::getListByClass($request->input('class_id'));

        return ResponseTool::jsonResponse(0, 'success', $data);
    }

    // 获取活动数量
    public function getCount(GetCountRequest $request)
    {
        $count = Activity::getCountByClass($request->input('class_id'), $request->input('query_word'));

        return ResponseTool::jsonResponse(0, 'success', $count);
    }

    // 根据班级获取活动
    public function getTable(GetTableRequest $request)
    {
        $queryCount = $request->input('query_count');
        $queryWord = $request->input('query_word');
        $classId = $request->input('class_id');

        $activity = Activity::getTableByClass(
            $classId,
            $queryWord,
            $queryCount
        );

        $allActivities = [];
        foreach ($activity as $item) {
            $activityData = [
                'activity_id' => $item->activity_id,
                'name' => $item->name,
                'progress' => Activity::getActivityProgress($item->activity_id),
                'start_time' => $item->start_time,
                'stop_time' => $item->stop_time,
                'status' => $item->status,
            ];
            array_push($allActivities, $activityData);
        }

        return ResponseTool::jsonResponse(0, 'success', $allActivities);
    }

    // 根据活动获取作业
    public function GetHomeworkTable(GetHomeworkTableRequest $request)
    {
        $queryCount = $request->input('query_count');
        $queryWord = $request->input('query_word');
        $activityId = $request->input('activity_id');
        $sortMode = $request->input('sort_mode');
        $sortField = $request->input('sort_field');

        if (! is_null($sortMode) && ! is_null($sortField)) {
            $users = Homework::getTableByActivityWithSort($activityId, $queryWord, $queryCount, $sortMode, $sortField);
        } else {
            $users = Homework::getTableByActivity($activityId, $queryWord, $queryCount);
        }

        $allActivities = [];

        foreach ($users as $row) {
            $status = 0;
            $submitDate = '';

            if (! is_null($row->update_time)) {
                $status = 1;
                $submitDate = $row->update_time;
            }

            $studentData = [
                'name' => $row->name,
                'account' => $row->account,
                'class_name' => ClassModel::getClassName($row->class_id),
                'status' => $status,
                'submit_date' => $submitDate,
            ];

            array_push($allActivities, $studentData);
        }

        return ResponseTool::jsonResponse(0, 'success', $allActivities);
    }

    // 导出活动 TODO
    public function export(ExportRequest $request)
    {
        $activityId = $request->input('activity_id');
        $times = $request->input('times');

        // 获取活动信息
        $activity = Activity::getInfo($activityId);
        if (! $activity) {
            return ResponseTool::jsonResponse(4, '活动不存在', null);
        }

        $activityName = $activity->name;
        $classId = $activity->class_id;
        $renameFormat = $activity->rename_format;

        // 获取班级信息
        $className = ClassModel::getClassName($classId);
        if (! $className) {
            return ResponseTool::jsonResponse(4, '班级不存在', null);
        }

        // 文件路径设置
        $basePath = "{$activityId}/{$classId}/";
        $fileName = "{$className} {$activityName}.zip";
        $fullPath = $basePath . $fileName;

        // 检查是否已有成品文件
        if (Storage::disk('output')->exists($fullPath) && $times != 'third') {
            if ($times == 'first') {
                // 返回选项
                return ResponseTool::jsonResponse(3, '已有输出文件', null);
            } elseif ($times == 'second') {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
                $storage = Storage::disk('output');
                return $storage->download($fullPath, $fileName);
            }
        }

        // 检查是否有原始文件可以导出
        if (! Storage::disk('homework')->exists($basePath)) {
            return ResponseTool::jsonResponse(4, '没有可导出的内容', null);
        }

        Storage::disk('temp')->deleteDirectory($basePath);
        Storage::disk('temp')->makeDirectory($basePath);
        Storage::disk('output')->deleteDirectory($basePath);
        Storage::disk('output')->makeDirectory($basePath);

        // 获取所有原始文件
        $files = Storage::disk('homework')->files($basePath);

        // 遍历并重命名文件
        foreach ($files as $file) {
            $filename = basename($file);

            if (in_array($filename, ['.', '..'])) {
                continue;
            }

            $parts = explode(' ', $filename);

            if (count($parts) < 3) {
                continue;
            }

            $name = $parts[1] ?? '';
            $accountWithExt = $parts[2] ?? '';

            $account = pathinfo($accountWithExt, PATHINFO_FILENAME);

            $extension = pathinfo($filename, PATHINFO_EXTENSION);

            $newFilename = $renameFormat;
            $newFilename = str_replace('[班级]', $className, $newFilename);
            $newFilename = str_replace('[姓名]', $name, $newFilename);
            $newFilename = str_replace('[学号]', $account, $newFilename);
            $newFilename = str_replace('[活动名]', $activityName, $newFilename);
            $newFilename .= '.' . $extension;

            // 复制并重命名文件
            $sourcePath = $file;
            $destinationPath = $basePath . $newFilename;

            // 从原始存储磁盘复制到临时磁盘
            $fileContent = Storage::disk('homework')->get($sourcePath);
            Storage::disk('temp')->put($destinationPath, $fileContent);
        }

        // 创建ZIP文件
        $zip = new \ZipArchive;

        // 使用 Storage::disk() 获取路径
        $fullZipPath = Storage::disk('output')->path($fullPath);

        // 确保目录存在
        $zipDir = dirname($basePath);
        if (!is_dir($zipDir)) {
            mkdir($zipDir, 0755, true);
        }

        if ($zip->open($fullZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            // 获取临时文件夹中的所有文件
            $tempFiles = Storage::disk('temp')->files($basePath);

            foreach ($tempFiles as $file) {
                if (basename($file) === '.' || basename($file) === '..') {
                    continue;
                }

                // 添加文件到ZIP
                $fileContent = Storage::disk('temp')->get($file);
                $zip->addFromString(basename($file), $fileContent);
            }

            $zip->close();

            // 清理临时文件夹
            Storage::disk('temp')->deleteDirectory($basePath);

            // 检查ZIP文件是否创建成功 - 使用 Storage::disk() 检查
            if (Storage::disk('output')->exists($fullPath)) {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
                $storage = Storage::disk('output');
                return $storage->download($fullPath, $fileName);
            } else {
                return ResponseTool::jsonResponse(1, 'ZIP文件创建失败', null);
            }
        } else {
            return ResponseTool::jsonResponse(1, '无法创建ZIP文件', null);
        }
    }
}
