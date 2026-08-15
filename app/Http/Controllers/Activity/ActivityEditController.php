<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\DeleteRequest;
use App\Http\Requests\Activity\EditRequest;
use App\Http\Requests\Activity\EditTimeRequest;
use App\Models\Activity;
use App\Models\ActivityLog;
use App\Utils\ResponseTool;
use App\Http\Requests\Activity\CreateRequest;
use App\Utils\RequestTool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;


class ActivityEditController extends Controller
{
    //创建活动
    public function create(CreateRequest $request)
    {
        $name = $request->input('name');
        $title = $request->input('title');
        $classId = $request->input('class_id');
        $requestment = $request->input('requestment');
        $renameFormat = $request->input('rename_format');
        $accept = $request->input('accept');
        $startTime = $request->input('start_time');
        $stopTime = $request->input('stop_time');

        $activityId = null;
        try {
            DB::transaction(function () use ($request, $name, $title, $classId, $requestment, $renameFormat, $accept, $startTime, $stopTime, &$activityId) {
                $activityId = Activity::create($name, $title, $classId, $requestment, $renameFormat, $accept, $startTime, $stopTime);

                foreach ($request->file('files', []) as $file) {
                    if ($file->isValid()) {
                        $stored = $file->storeAs($activityId, RequestTool::xxs($file->getClientOriginalName()), 'publish');
                        if ($stored === false) {
                            throw new RuntimeException('活动附件保存失败');
                        }
                    }
                }
            });
        } catch (RuntimeException $exception) {
            if ($activityId) {
                Storage::disk('publish')->deleteDirectory($activityId);
            }
            return ResponseTool::jsonResponse(1, $exception->getMessage(), null, 500);
        }

        ActivityLog::log(
            $request->user()->account,
            $activityId,
            "create",
            $request->ip()
        );

        return ResponseTool::jsonResponse(0, 'success', $activityId);
    }

    // 编辑活动
    public function edit(EditRequest $request)
    {
        $activityId = $request->input('activity_id');
        $fileUpload = $request->input('file_upload');
        $name = $request->input('name');
        $title = $request->input('title');
        $classId = Activity::getClass($activityId);
        $requestment = $request->input('requestment');
        $renameFormat = $request->input('rename_format');
        $accept = $request->input('accept');
        $startTime = $request->input('start_time');
        $stopTime = $request->input('stop_time');

        try {
            DB::transaction(function () use ($request, $activityId, $name, $title, $classId, $requestment, $renameFormat, $accept, $startTime, $stopTime, $fileUpload) {
                $updated = Activity::edit($activityId, $name, $title, $classId, $requestment, $renameFormat, $accept, $startTime, $stopTime);
                if (!$updated) {
                    throw new RuntimeException('活动不存在或内容没有变化');
                }

                if ($fileUpload === 'true') {
                    $this->replacePublishedFiles($activityId, $request->file('files', []));
                }
            });
        } catch (RuntimeException $exception) {
            return ResponseTool::jsonResponse(1, $exception->getMessage(), null, 500);
        }

        ActivityLog::log(
            $request->user()->account,
            $activityId,
            "edit",
            $request->ip()
        );

        return ResponseTool::jsonResponse(0, 'success', $activityId);
    }

    // 编辑活动时间
    public function editTime(EditTimeRequest $request)
    {
        $activityId = $request->input('activity_id');
        $startTime = $request->input('start_time');
        $stopTime = $request->input('stop_time');

        Activity::editTime($activityId, $startTime, $stopTime);

        ActivityLog::log(
            $request->user()->account,
            $activityId,
            "edit",
            $request->ip()
        );

        return ResponseTool::jsonResponse(0, 'success', $activityId);
    }

    //删除活动
    public function delete(DeleteRequest $request)
    {
        $activityId = $request->input('activity_id');

        if (!Activity::deleteWithRelations($activityId)) {
            return ResponseTool::jsonResponse(1, '活动不存在', null, 404);
        }

        ActivityLog::log(
            $request->user()->account,
            $activityId,
            "deleteedit",
            $request->ip()
        );

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    private function replacePublishedFiles(string $activityId, array $files): void
    {
        $storage = Storage::disk('publish');
        $operationId = (string) Str::uuid();
        $stagingDirectory = ".replacements/{$activityId}-{$operationId}";
        $backupDirectory = ".backups/{$activityId}-{$operationId}";
        $swapping = false;

        try {
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $stored = $file->storeAs(
                        $stagingDirectory,
                        RequestTool::xxs($file->getClientOriginalName()),
                        'publish'
                    );
                    if ($stored === false) {
                        throw new RuntimeException('新附件暂存失败');
                    }
                }
            }

            $swapping = true;
            foreach ($storage->files($activityId) as $oldFile) {
                if (!$storage->move($oldFile, $backupDirectory . '/' . basename($oldFile))) {
                    throw new RuntimeException('旧附件备份失败');
                }
            }

            foreach ($storage->files($stagingDirectory) as $newFile) {
                if (!$storage->move($newFile, $activityId . '/' . basename($newFile))) {
                    throw new RuntimeException('新附件替换失败');
                }
            }

            $storage->deleteDirectory($backupDirectory);
        } catch (\Throwable $exception) {
            if ($swapping) {
                $storage->deleteDirectory($activityId);
                foreach ($storage->files($backupDirectory) as $backupFile) {
                    $storage->move($backupFile, $activityId . '/' . basename($backupFile));
                }
            }
            throw new RuntimeException($exception->getMessage(), 0, $exception);
        } finally {
            $storage->deleteDirectory($stagingDirectory);
            $storage->deleteDirectory($backupDirectory);
        }
    }
}
