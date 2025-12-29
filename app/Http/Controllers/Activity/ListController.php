<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\GetCountRequest;
use App\Http\Requests\Activity\GetListRequest;
use App\Http\Requests\Activity\GetTableRequest;
use App\Http\Requests\Activity\ExportRequest;
use App\Http\Requests\Activity\GetHomeworkTableRequest;
use App\Utils\ResponseTool;
use App\Models\Activity;
use App\Models\ClassModel;
use App\Models\Homework;

class ListController extends Controller
{

    // 编辑列表:列出活动
    public function getList(GetListRequest $request)
    {
        $data = Activity::getListByClass($request->input("class_id"));
        return ResponseTool::jsonResponse(0, 'success', $data);
    }

    // 获取活动数量
    public function getCount(GetCountRequest $request)
    {
        $count = Activity::getCountByClass($request->input("class_id"), $request->input("query_word"));
        return ResponseTool::jsonResponse(0, 'success', $count);
    }

    // 根据班级获取活动
    public function getTable(GetTableRequest $request)
    {
        $queryCount = $request->input('query_count');
        $queryWord = $request->input('query_word');
        $classId = $request->input('class_id');

        $activity = Activity::getTableByClass(
            $classId,
            $queryWord,
            $queryCount
        );

        $all_activities = [];
        foreach ($activity as $item) {
            $activityData = array(
                'activity_id' => $item->activity_id,
                'name' => $item->name,
                'progress' => Activity::getActivityProgress($item->activity_id),
                'start_time' => $item->start_time,
                'stop_time' => $item->stop_time,
                'status' => $item->status,
            );
            array_push($all_activities, $activityData);
        }
        return ResponseTool::jsonResponse(0, 'success', $all_activities);
    }


    // 根据活动获取作业
    public function GetHomeworkTable(GetHomeworkTableRequest $request)
    {
        $queryCount = $request->input('query_count');
        $queryWord = $request->input('query_word');
        $activityId = $request->input('activity_id');
        $sortMode = $request->input('sort_mode');
        $sortField = $request->input('sort_field');

        if (!is_null($sortMode) && !is_null($sortField)) {
            $users = Homework::getTableByActivityWithSort($activityId, $queryWord, $queryCount,  $sortMode, $sortField);
        } else {
            $users = Homework::getTableByActivity($activityId,   $queryWord,  $queryCount);
        }

        $all_activities = [];

        foreach ($users as $row) {
            $status = 0;
            $submitDate = "";

            if (!is_null($row->update_time)) {
                $status = 1;
                $submitDate = $row->update_time;
            }

            $studentData = [
                'name' => $row->name,
                'account' => $row->account,
                'class_name' => ClassModel::getClassName($row->class_id),
                'status' => $status,
                'submit_date' => $submitDate,
            ];

            array_push($all_activities, $studentData);
        }

        return ResponseTool::jsonResponse(0, 'success', $all_activities);
    }


    //导出活动 TODO 
    public function export(ExportRequest $request)
    {

        $activity = Activity::getInfo($request->input('activity_id'));
        $times = $request->input('times');

        $path = "";

        if (file_exists($path) && $times != "third") {
            ResponseTool::jsonResponse(3, "have output", NULL);
        } else if ($times == "second") {            // 直接返回路径
            ResponseTool::jsonResponse(0, "success", $path);
        }

        // 不存在则生成成品并返回

        // 定位路径

        // 批量重命名

        // 打包zip

        return ResponseTool::jsonResponse(0, 'success', null);
    }
}
