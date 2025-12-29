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
        $activity_id = $request->input('activity_id');
        $file_upload = $request->input('file_upload');
        $name = $request->input('name');
        $title = $request->input('title');
        $class_id = $request->input('class_id');
        $requestment = $request->input('requestment');
        $rename_format = $request->input('rename_format');
        $accept = $request->input('accept');
        $start_time = $request->input('start_time');
        $stop_time = $request->input('stop_time');

        Activity::edit($activity_id, $name, $title, $class_id, $requestment, $rename_format, $accept, $start_time, $stop_time, $file_upload);
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 编辑活动时间
    public function editTime(EditTimeRequest $request)
    {
        $activity_id = $request->input('activity_id');
        $start_time = $request->input('start_time');
        $stop_time = $request->input('stop_time');

        Activity::editTime($activity_id, $start_time, $stop_time);
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 获取单活动详情用于填充表单 TODO 活动鉴权
    public function getInfo(GetInfoRequest $request)
    {
        $data = Activity::getInfo($request->input('activity_id'));
        $data['files'] = [];
        return ResponseTool::jsonResponse(0, 'success', $data);
    }

    //删除活动 TODO 鉴权
    public function delete(DeleteRequest $request)
    {
        Activity::query()->where('activity_id', $request->input('activity_id'))->first()->delete();
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    //导出活动 TODO 
    public function export(Request $request)
    {
        return ResponseTool::jsonResponse(0, 'success', null);
    }
}
