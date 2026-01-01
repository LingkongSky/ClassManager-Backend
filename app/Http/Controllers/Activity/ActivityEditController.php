<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\DeleteRequest;
use App\Http\Requests\Activity\EditRequest;
use App\Http\Requests\Activity\EditTimeRequest;
use App\Http\Requests\Activity\GetInfoRequest;
use App\Models\Activity;
use App\Models\ActivityLog;
use App\Utils\ResponseTool;
use App\Http\Requests\Activity\CreateRequest;
use App\Utils\RequestTool;
use Illuminate\Support\Facades\Storage;


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

        $activityId = Activity::create($name, $title, $classId, $requestment, $renameFormat, $accept, $startTime, $stopTime);

        $files = $request->file('files');
        if (!empty($files)) {
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $file->storeAs($activityId, RequestTool::xxs($file->getClientOriginalName()), 'publish');
                }
            }
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

        Activity::edit($activityId, $name, $title, $classId, $requestment, $renameFormat, $accept, $startTime, $stopTime, $fileUpload);

        if ($fileUpload == "true") {
            // 删除旧文件
            $storage = Storage::disk('publish');
            $storage->delete($activityId);

            $files = $request->file('files');
            if (!empty($files)) {
                foreach ($files as $file) {
                    if ($file->isValid()) {
                        $file->storeAs($activityId, RequestTool::xxs($file->getClientOriginalName()), 'publish');
                    }
                }
            }
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

    // 获取单活动详情用于填充表单
    public function getInfo(GetInfoRequest $request)
    {
        $data = Activity::getInfo($request->input('activity_id'));
        $data['files'] = [];
        return ResponseTool::jsonResponse(0, 'success', $data);
    }

    //删除活动
    public function delete(DeleteRequest $request)
    {
        $activityId = $request->input('activity_id');

        Activity::query()->where('activity_id', $activityId)->first()->delete();

        ActivityLog::log(
            $request->user()->account,
            $activityId,
            "deleteedit",
            $request->ip()
        );

        return ResponseTool::jsonResponse(0, 'success', null);
    }
}
