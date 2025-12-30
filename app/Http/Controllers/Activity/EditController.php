<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\DeleteRequest;
use App\Http\Requests\Activity\EditRequest;
use App\Http\Requests\Activity\EditTimeRequest;
use App\Http\Requests\Activity\GetInfoRequest;
use App\Models\Activity;
use App\Utils\ResponseTool;
use App\Http\Requests\Request;

class EditController extends Controller
{
    // TODO 处理文件校验 存放与转移

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
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 编辑活动时间
    public function editTime(EditTimeRequest $request)
    {
        $activityId = $request->input('activity_id');
        $startTime = $request->input('start_time');
        $stopTime = $request->input('stop_time');

        Activity::editTime($activityId, $startTime, $stopTime);
        return ResponseTool::jsonResponse(0, 'success', null);
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
        Activity::query()->where('activity_id', $request->input('activity_id'))->first()->delete();
        return ResponseTool::jsonResponse(0, 'success', null);
    }

}