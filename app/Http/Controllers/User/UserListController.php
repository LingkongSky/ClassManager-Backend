<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\GetCountRequest;
use App\Http\Requests\User\GetListRequest;
use App\Models\ClassModel;
use App\Models\Student;
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
            $classIds = Student::getUserClasses($item->account);
            $primaryClassId = $classIds[0] ?? null;
            $userData = array(
                'name' => $item->name,
                'account' => $item->account,
                'class_ids' => $classIds,
                'class_names' => array_map(fn($id) => ClassModel::getClassName($id), $classIds),
                'primary_class_id' => $primaryClassId,
                'primary_class_name' => $primaryClassId ? ClassModel::getClassName($primaryClassId) : null,
                'class_id' => $primaryClassId,
                'class_name' => $primaryClassId ? ClassModel::getClassName($primaryClassId) : null,
                'can_edit' => $request->user()->canManageStudentByPrimaryClass($item->account),
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
