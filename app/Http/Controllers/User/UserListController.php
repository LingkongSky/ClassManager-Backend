<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\GetCountRequest;
use App\Http\Requests\User\GetListRequest;
use App\Models\ClassModel;
use App\Utils\ResponseTool;

class UserListController extends Controller
{
    // 获取班级列表
    public function getList(GetListRequest $request)
    {
        $queryCount = $request->input('query_count');
        $queryWord = $request->input('query_word');
        $classId = $request->input('class_id');

        $users = $request->user()->getStudentByClass(
            $classId,
            $queryWord,
            $queryCount
        );

        $allUser = [];
        foreach ($users as $item) {
            $userData = array(
                'name' => $item->name,
                'account' => $item->account,
                'class_id' => $item->class_id,
                'class_name' => ClassModel::getClassName($item->class_id),
            );
            array_push($allUser, $userData);
        }
        return ResponseTool::jsonResponse(0, 'success', $allUser);
    }

    // 获取班级的学生数量
    public function getCount(GetCountRequest $request)
    {
        return ResponseTool::jsonResponse(0, 'success', $request->user()->getCountByClass($request->input("class_id"), $request->input("query_word")));
    }
}
