<?php

namespace App\Http\Controllers\Todos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Models\Homework;
use App\Models\Activity;
use App\Http\Requests\Todos\OpenRequest;
use App\Http\Requests\Todos\GetStartedRequest;
use App\Http\Requests\Todos\DownloadRequest;
use App\Http\Requests\Todos\DownloadAdminRequest;
use App\Models\DownloadLog;
use App\Utils\ResponseTool;
use Illuminate\Support\Facades\Storage;

class TodosListController extends Controller
{
    // 获取活动
    public function getTodos(Request $request)
    {
        if (empty($request->user()->class_id)) {
            return ResponseTool::jsonResponse(0, 'success', []);
        }

        $todos = $request->user()->getTodos();
        $account = $request->user()->account;

        $allTodos = [];
        foreach ($todos as $item) {
            $todosData = array(
                'activity_id' => $item->activity_id,
                'title' => $item->title,
                'name' => $item->name,
                'start_time' => $item->start_time,
                'stop_time' => $item->stop_time,
                'type' => $item->type,
                'is_finished' => Homework::hasSubmit($account, $item->activity_id)
            );

            array_push($allTodos, $todosData);
        }
        return ResponseTool::jsonResponse(0, 'success', $allTodos);
    }

    // 获取活动数量
    public function getTodosCount(Request $request)
    {
        if (empty($request->user()->class_id)) {
            return ResponseTool::jsonResponse(0, 'success', 0);
        }

        $count = Activity::getTodosCountByClass($request->user()->class_id);
        return ResponseTool::jsonResponse(0, 'success', $count);
    }


    // 获取已开始的活动
    public function getStarted(GetStartedRequest $request)
    {
        if (empty($request->user()->class_id)) {
            return ResponseTool::jsonResponse(0, 'success', []);
        }

        $todos = $request->user()->GetStarted();
        $account = $request->user()->account;

        $allTodos = [];
        foreach ($todos as $item) {
            $todosData = array(
                'activity_id' => $item->activity_id,
                'title' => $item->title,
                'name' => $item->name,
                'start_time' => $item->start_time,
                'stop_time' => $item->stop_time,
                'type' => $item->type,
                'status' => $item->status,
                'is_finished' => Homework::hasSubmit($account, $item->activity_id)
            );

            array_push($allTodos, $todosData);
        }
        return ResponseTool::jsonResponse(0, 'success', $allTodos);
    }

    // 获取已开始的活动数量
    public function getStartedCount(Request $request)
    {
        if (empty($request->user()->class_id)) {
            return ResponseTool::jsonResponse(0, 'success', 0);
        }

        $count = Activity::getStartedTodosCountByClass($request->user()->class_id);
        return ResponseTool::jsonResponse(0, 'success', $count);
    }

    // 打开活动
    public function open(OpenRequest $request)
    {
        $data = Activity::getInfo($request->input('activity_id'));
        $data['files'] = [];
        return ResponseTool::jsonResponse(0, 'success', $data);
    }

    // 下载作业
    public function download(DownloadRequest $request)
    {
        $activity = Activity::getInfo($request->input('activity_id'));

        // 判断活动状态是否为1
        if ($activity->status != 1) {
            return ResponseTool::jsonResponse(1, 'failed', null);
        }

        // 检测是否提交
        $homework = Homework::getSubmitInfo($request->user()->account, $activity->activity_id);
        if (!$homework) {
            return ResponseTool::jsonResponse(1, 'failed', null);
        }

        $filePath = "{$activity->activity_id}/{$activity->class_id}/{$homework->file_name}";

        // 检查文件是否存在
        if (!Storage::disk('homework')->exists($filePath)) {
            return ResponseTool::jsonResponse(2, '文件不存在', null);
        }

        // 记录下载日志
        DownloadLog::log($request->user()->account, $homework->file_name, 'publish', 'successful', $request->ip());

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk('homework');
        return $storage->download($filePath, $homework->file_name);
    }

    // 管理员下载作业
    public function downloadAdmin(DownloadAdminRequest $request)
    {
        $activity = Activity::getInfo($request->input('activity_id'));
        $targetAccount = $request->input('target_account');

        // 检测是否提交
        $homework = Homework::getSubmitInfo($targetAccount, $activity->activity_id);
        if (!$homework) {
            return ResponseTool::jsonResponse(1, 'failed', null);
        }

        $filePath = "{$activity->activity_id}/{$activity->class_id}/{$homework->file_name}";

        if (!Storage::disk('homework')->exists($filePath)) {
            return ResponseTool::jsonResponse(1, '文件不存在', null);
        }

        DownloadLog::log($request->user()->account, $homework->file_name, 'publish', 'successful', $request->ip());

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk('homework');
        return $storage->download($filePath, $homework->file_name);
    }
}
