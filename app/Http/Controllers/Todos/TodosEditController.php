<?php

namespace App\Http\Controllers\Todos;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\Activity;
use App\Models\ClassModel;
use App\Models\HomeworkLog;
use App\Models\Student;
use App\Http\Requests\Todos\SubmitAdminRequest;
use App\Http\Requests\Todos\SubmitRequest;
use App\Http\Requests\Todos\DeleteRequest;
use App\Http\Requests\Todos\DeleteAdminRequest;
use App\Utils\ResponseTool;
use Illuminate\Support\Facades\Storage;

class TodosEditController extends Controller
{
    // 提交作业
    public function submit(SubmitRequest $request)
    {

        $activity = Activity::getInfo($request->input('activity_id'));

        // 判断活动状态是否为1
        if ($activity->status != 1 && $request->user()->level == 0) {
            return ResponseTool::jsonResponse(1, 'failed', null);
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $className = ClassModel::getClassName($activity->class_id);
        $account  = $request->user()->account;
        $name = $request->user()->name;
        $newName = "{$className} {$name} {$account}.{$extension}";

        // 提交作业
        Homework::submitHomework($activity->activity_id, $request->user()->account, $activity->class_id, $newName);

        // 移动作业
        $file->storeAs(
            "{$activity->activity_id}/{$activity->class_id}",
            $newName,
            'homework'
        );

        HomeworkLog::log($request->user()->account, $newName, $activity->class_id, $request->input('activity_id'), 'submit', $request->ip());

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
            "{$activity->activity_id}/{$activity->class_id}",
            $newName,
            'homework'
        );

        HomeworkLog::log($request->user()->account, $newName, $activity->class_id, $request->input('activity_id'), "Admin submit {$targetAccount}", $request->ip());

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除提交的作业
    public function delete(DeleteRequest $request)
    {
        $activity = Activity::getInfo($request->input('activity_id'));

        // 判断活动状态是否为1
        if ($activity->status != 1 && $request->user()->level == 0) {
            return ResponseTool::jsonResponse(1, 'failed', null);
        }

        // 判断是否已提交
        $homework = Homework::getSubmitInfo($request->user()->account, $activity->activity_id);
        if (!$homework) {
            return ResponseTool::jsonResponse(1, 'failed', null);
        }

        // 删除文件
        $filePath = "{$activity->activity_id}/{$activity->class_id}/{$homework->file_name}";

        $storage = Storage::disk('homework');
        $storage->delete($filePath);
        

        // 删除提交
        Homework::deleteSubmit($request->user()->account, $activity->activity_id);

        HomeworkLog::log($request->user()->account, $homework->file_name, $activity->class_id, $request->input('activity_id'), 'delete', $request->ip());

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
        $filePath = "{$activity->activity_id}/{$activity->class_id}/{$homework->file_name}";

        $storage = Storage::disk('homework');
        $storage->delete($filePath);
        

        // 删除提交
        Homework::deleteSubmit($targetAccount, $activity->activity_id);

        HomeworkLog::log($request->user()->account, $homework->file_name, $activity->class_id, $request->input('activity_id'), "Admin delete {$targetAccount}", $request->ip());

        return ResponseTool::jsonResponse(0, 'success', null);
    }
}
