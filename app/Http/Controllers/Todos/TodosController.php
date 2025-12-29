<?php

namespace App\Http\Controllers\Todos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Models\Homework;
use App\Models\Activity;
use App\Http\Requests\Todos\OpenRequest;
use App\Http\Requests\Todos\GetStartedRequest;
use App\Utils\ResponseTool;

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

        $all_todos = [];
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

            array_push($all_todos, $todosData);
        }
        return ResponseTool::jsonResponse(0, 'success', $all_todos);
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

        $all_todos = [];
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

            array_push($all_todos, $todosData);
        }
        return ResponseTool::jsonResponse(0, 'success', $all_todos);
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


    // 打开活动 TODO 鉴权
    public function open(OpenRequest $request)
    {
        $data = Activity::getInfo($request->input('activity_id'));
        $data['files'] = [];
        return ResponseTool::jsonResponse(0, 'success', $data);
    }

    public function delete(Request $request) {}
    public function deleteAdmin(Request $request) {}


    public function download(Request $request) {}
    public function downloadAdmin(Request $request) {}

    public function submit(Request $request) {}
    public function submitAdmin(Request $request) {}
}
