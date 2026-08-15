<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddRequest;
use App\Http\Requests\User\DeleteRequest;
use App\Http\Requests\User\EditRequest;
use App\Http\Requests\User\EditPasswordRequest;
use App\Utils\ResponseTool;

class UserEditController extends Controller
{

    // 添加学生
    public function add(AddRequest $request)
    {
        $studentList = $request->input("data");
        foreach ($studentList as $item) {
            $request->user()->createStudent(
                $item['account'],
                $item['name'],
                $item['password'],
                is_array($item['class_ids'] ?? null) ? $item['class_ids'] : [],
            );
        }
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除学生
    public function delete(DeleteRequest $request)
    {
        $studentList = $request->input("student_list");

        foreach ($studentList as $item) {
            $result = $request->user()->deleteByAccount($item);
            if ($result === null) {
                return ResponseTool::jsonResponse(2, '无权限：仅可管理主班级在权限组内的学生', null, 403);
            }
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }
    
    // 修改用户
    public function edit(EditRequest $request)
    {
        $account = $request->input('account');
        $name = $request->input('name');
        $classIds = $request->input('class_ids');
        $primaryClassId = $request->input('primary_class_id');
        $result = $request->user()->edit($account, $name, $classIds, $primaryClassId);
        if ($result === null) {
            return ResponseTool::jsonResponse(2, '无权限：仅可管理主班级在权限组内的学生', null, 403);
        }
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 修改用户密码
    public function editPassword(EditPasswordRequest $request)
    {
        $result = $request->user()->editPassword($request->input('account'), $request->input('password'));
        if ($result === null) {
            return ResponseTool::jsonResponse(2, '无权限：仅可管理主班级在权限组内的学生', null, 403);
        }
        return ResponseTool::jsonResponse(0, 'success', null);
    }
}
