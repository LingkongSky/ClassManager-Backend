<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\CreateRequest;
use App\Models\Activity;
use App\Utils\ResponseTool;

class CreateController extends Controller
{

    // TODO 处理文件校验 存放与转移

    //创建活动
    public function create(CreateRequest $request)
    {
        $name = $request->input('name');
        $title = $request->input('title');
        $class_id = $request->input('class_id');
        $requestment = $request->input('requestment');
        $rename_format = $request->input('rename_format');
        $accept = $request->input('accept');
        $start_time = $request->input('start_time');
        $stop_time = $request->input('stop_time');

        Activity::create($name, $title, $class_id, $requestment, $rename_format, $accept, $start_time, $stop_time);
        return ResponseTool::jsonResponse(0, 'success', null);
    }
}
