<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\ExportRequest;
use App\Http\Requests\Activity\GetCountRequest;
use App\Http\Requests\Activity\GetHomeworkTableRequest;
use App\Http\Requests\Activity\GetListRequest;
use App\Http\Requests\Activity\GetTableRequest;
use App\Http\Requests\Activity\GetInfoRequest;
use App\Models\Activity;
use App\Models\ClassModel;
use App\Models\Homework;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use App\Utils\ResponseTool;
use App\Utils\ZipTool;

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

        if(!$request->user()->isClassInPermission($classId)){
            return  ResponseTool::jsonResponse(1, 'failed', null);
        }

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
            $classIds = Student::getUserClasses($row->account);
            $primaryClassId = $classIds[0] ?? null;
            $classNames = array_map(fn($id) => ClassModel::getClassName($id), $classIds);
            $primaryClassName = $primaryClassId ? ClassModel::getClassName($primaryClassId) : null;

            if (! is_null($row->update_time)) {
                $status = 1;
                $submitDate = $row->update_time;
            }

            $studentData = [
                'name' => $row->name,
                'account' => $row->account,
                'class_ids' => $classIds,
                'class_names' => $classNames,
                'primary_class_id' => $primaryClassId,
                'primary_class_name' => $primaryClassName,
                'class_name' => $primaryClassName,
                'status' => $status,
                'submit_date' => $submitDate,
            ];

            array_push($allActivities, $studentData);
        }

        return ResponseTool::jsonResponse(0, 'success', $allActivities);
    }

    // 获取单活动详情用于填充表单
    public function getInfo(GetInfoRequest $request)
    {
        $data = Activity::getInfo($request->input('activity_id'));
        $data['files'] = [];
        return ResponseTool::jsonResponse(0, 'success', $data);
    }

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

        // 压缩目标文件夹
        ZipTool::compressFileFolder(Storage::disk('output')->path($fullPath),Storage::disk('temp')->path($basePath));
        
        Storage::disk('temp')->deleteDirectory($basePath);
        
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage **/   
        $storage = Storage::disk('output');
        return $storage->download($fullPath, $fileName);
    }
}
