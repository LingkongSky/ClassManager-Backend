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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

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

        try {
            $this->storeHomeworkSubmission($activity, $request->user()->account, $newName, $file);
        } catch (RuntimeException $exception) {
            return ResponseTool::jsonResponse(1, $exception->getMessage(), null, 500);
        }

        HomeworkLog::log($request->user()->account, $newName, $activity->class_id, $request->input('activity_id'), 'submit', $request->ip());

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 管理员提交作业
    public function submitAdmin(SubmitAdminRequest $request)
    {

        $activity = Activity::getInfo($request->input('activity_id'));
        $targetAccount = $request->input('target_account');

        // 判断学生是否与活动处于同一班级
        if (!Student::hasClass($targetAccount, $activity->class_id)) {
            return ResponseTool::jsonResponse(1, 'failed', null);
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $className = ClassModel::getClassName($activity->class_id);
        $accountName = Student::getName($targetAccount);
        $newName = "{$className} {$accountName} {$targetAccount}.{$extension}";

        try {
            $this->storeHomeworkSubmission($activity, $targetAccount, $newName, $file);
        } catch (RuntimeException $exception) {
            return ResponseTool::jsonResponse(1, $exception->getMessage(), null, 500);
        }

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

        $filePath = "{$activity->activity_id}/{$activity->class_id}/{$homework->file_name}";
        DB::transaction(function () use ($request, $activity, $filePath) {
            Homework::deleteSubmit($request->user()->account, $activity->activity_id);
            DB::afterCommit(fn() => Storage::disk('homework')->delete($filePath));
        });

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

        $filePath = "{$activity->activity_id}/{$activity->class_id}/{$homework->file_name}";
        DB::transaction(function () use ($targetAccount, $activity, $filePath) {
            Homework::deleteSubmit($targetAccount, $activity->activity_id);
            DB::afterCommit(fn() => Storage::disk('homework')->delete($filePath));
        });

        HomeworkLog::log($request->user()->account, $homework->file_name, $activity->class_id, $request->input('activity_id'), "Admin delete {$targetAccount}", $request->ip());

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    private function storeHomeworkSubmission(Activity $activity, string $account, string $newName, $file): void
    {
        $storage = Storage::disk('homework');
        $operationId = (string) Str::uuid();
        $stagingDirectory = ".staging/{$operationId}";
        $backupDirectory = ".backups/{$operationId}";
        $targetDirectory = "{$activity->activity_id}/{$activity->class_id}";
        $targetPath = "{$targetDirectory}/{$newName}";
        $oldHomework = Homework::getSubmitInfo($account, $activity->activity_id);
        $oldPath = $oldHomework
            ? "{$activity->activity_id}/{$activity->class_id}/{$oldHomework->file_name}"
            : null;
        $oldFileMoved = false;
        $newFileMoved = false;

        try {
            $staged = $file->storeAs($stagingDirectory, $newName, 'homework');
            if ($staged === false) {
                throw new RuntimeException('作业文件暂存失败');
            }

            if ($oldPath && $storage->exists($oldPath)) {
                if (!$storage->move($oldPath, $backupDirectory . '/' . basename($oldPath))) {
                    throw new RuntimeException('旧作业文件备份失败');
                }
                $oldFileMoved = true;
            }

            if (!$storage->move($staged, $targetPath)) {
                throw new RuntimeException('作业文件保存失败');
            }
            $newFileMoved = true;

            DB::transaction(function () use ($activity, $account, $newName) {
                Homework::submitHomework($activity->activity_id, $account, $activity->class_id, $newName);
            });

            $storage->deleteDirectory($backupDirectory);
        } catch (\Throwable $exception) {
            if ($newFileMoved) {
                $storage->delete($targetPath);
            }
            if ($oldFileMoved && $oldPath) {
                foreach ($storage->files($backupDirectory) as $backupFile) {
                    $storage->move($backupFile, $oldPath);
                }
            }
            throw new RuntimeException($exception->getMessage(), 0, $exception);
        } finally {
            $storage->deleteDirectory($stagingDirectory);
            $storage->deleteDirectory($backupDirectory);
        }
    }
}
