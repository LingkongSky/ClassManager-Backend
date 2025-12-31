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
                $item['class_id'],
            );
        }
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除学生
    public function delete(DeleteRequest $request)
    {
        $studentList = $request->input("student_list");

        foreach ($studentList as $item) {
            $request->user()->deleteByAccount($item);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }
    // 修改用户
    public function edit(EditRequest $request)
    {
        $account = $request->input('account');
        $name = $request->input('name');
        $classId = $request->input('class_id');
        $request->user()->edit($account, $name, $classId);
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 修改用户密码
    public function editPassword(EditPasswordRequest $request)
    {
        $request->user()->editPassword($request->input('account'), $request->input('password'));
        return ResponseTool::jsonResponse(0, 'success', null);
    }
}
