<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\CreateRequest;
use App\Models\Activity;
use App\Models\ActivityLog;
use App\Utils\ResponseTool;

class CreateController extends Controller
{

    // TODO 处理文件校验 存放与转移

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

        ActivityLog::log(
            $request->user()->account,
            $activityId,
            "create",
            $request->ip()
        );
        
        return ResponseTool::jsonResponse(0, 'success', null);
    }
}
