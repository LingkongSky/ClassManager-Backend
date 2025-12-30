<?php

namespace App\Http\Controllers\Todos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Models\Homework;
use App\Models\Activity;
use App\Models\ClassModel;
use App\Models\HomeworkLog;
use App\Models\Student;
use App\Http\Requests\Todos\OpenRequest;
use App\Http\Requests\Todos\GetStartedRequest;
use App\Http\Requests\Todos\SubmitAdminRequest;
use App\Http\Requests\Todos\SubmitRequest;
use App\Http\Requests\Todos\DeleteRequest;
use App\Http\Requests\Todos\DeleteAdminRequest;
use App\Http\Requests\Todos\DownloadRequest;
use App\Http\Requests\Todos\DownloadAdminRequest;
use App\Models\DownloadLog;
use App\Utils\ResponseTool;
use Illuminate\Support\Facades\Storage;

class TodosController extends Controller
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

    // 提交作业
    public function submit(SubmitRequest $request)
    {

        $activity = Activity::getInfo($request->input('activity_id'));

        // 判断活动状态是否为1
        if ($activity->status != 1) {
            return ResponseTool::jsonResponse(1, 'failed', null);
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $className = ClassModel::getClassName($activity->class_id);
        $newName = "{$className} {$request->user()->name} {$request->user()->account}.{$extension}";

        // 提交作业
        Homework::submitHomework($activity->activity_id, $request->user()->account, $activity->class_id, $newName);

        // 移动作业
        $file->storeAs(
            "homework/{$activity->activity_id}/{$activity->class_id}",
            $newName,
            'activity'
        );

        HomeworkLog::log($request->user()->account,$newName,$activity->class_id,$request->input('activity_id'),'submit',$request->ip());

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 管理员提交作业
    public function submitAdmin(SubmitAdminRequest $request)
    {

        $activity = Activity::getInfo($request->input('activity_id'));
        $targetAccount = $request->input('target_account');

        // 判断学生是否与活动处于同一班级
        if ($activity->class_id != Student::getUserClass($targetAccount)) {
            return ResponseTool::jsonResponse(1, 'failed', null);
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $className = ClassModel::getClassName($activity->class_id);
        $accountName = Student::getName($targetAccount);
        $newName = "{$className} {$accountName} {$targetAccount}.{$extension}";

        // 提交作业
        Homework::submitHomework($activity->activity_id, $targetAccount, $activity->class_id, $newName);

        // 移动文件
        $file->storeAs(
            "homework/{$activity->activity_id}/{$activity->class_id}",
            $newName,
            'activity'
        );

        HomeworkLog::log($request->user()->account,$newName,$activity->class_id,$request->input('activity_id'),"Admin submit {$targetAccount}",$request->ip());

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除提交的作业
    public function delete(DeleteRequest $request)
    {
        $activity = Activity::getInfo($request->input('activity_id'));

        // 判断活动状态是否为1
        if ($activity->status != 1) {
            return ResponseTool::jsonResponse(1, 'failed', null);
        }

        // 判断是否已提交
        $homework = Homework::getSubmitInfo($request->user()->account, $activity->activity_id);
        if (!$homework) {
            return ResponseTool::jsonResponse(1, 'failed', null);
        }

        // 删除文件
        $filePath = "homework/{$activity->activity_id}/{$activity->class_id}/{$homework->file_name}";

        $storage = Storage::disk('activity');
        if ($storage->exists($filePath)) {
            $storage->delete($filePath);
        }

        // 删除提交
        Homework::deleteSubmit($request->user()->account, $activity->activity_id);

        HomeworkLog::log($request->user()->account,$homework->file_name,$activity->class_id,$request->input('activity_id'),'delete',$request->ip());

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 管理员删除提交的作业
    public function deleteAdmin(DeleteAdminRequest $request)
    {

        $activity = Activity::getInfo($request->input('activity_id'));
        $targetAccount = $request->input('target_account');

        // 判断是否已提交
        $homework = Homework::getSubmitInfo($targetAccount, $activity->activity_id);
        if (!$homework) {
            return ResponseTool::jsonResponse(1, 'failed', null);
        }

        // 删除文件
        $filePath = "homework/{$activity->activity_id}/{$activity->class_id}/{$homework->file_name}";

        $storage = Storage::disk('activity');
        if ($storage->exists($filePath)) {
            $storage->delete($filePath);
        }

        // 删除提交
        Homework::deleteSubmit($targetAccount, $activity->activity_id);

        HomeworkLog::log($request->user()->account,$homework->file_name,$activity->class_id,$request->input('activity_id'),"Admin delete {$targetAccount}",$request->ip());

        return ResponseTool::jsonResponse(0, 'success', null);
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

        $filePath = "/download/homework/{$activity->activity_id}/{$activity->class_id}/{$homework->file_name}";

        DownloadLog::log($request->user()->account, $homework->file_name,'publish' ,'successful' ,$request->ip());

        return ResponseTool::jsonResponse(0, 'success', $filePath);
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

        $filePath = "/download/homework/{$activity->activity_id}/{$activity->class_id}/{$homework->file_name}";

        DownloadLog::log($request->user()->account, $homework->file_name,'publish' ,'successful' ,$request->ip());

        return ResponseTool::jsonResponse(0, 'success', $filePath);
    }
}
