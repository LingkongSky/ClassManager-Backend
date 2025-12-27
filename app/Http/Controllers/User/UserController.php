<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddRequest;
use App\Http\Requests\User\DeleteRequest;
use App\Http\Requests\User\EditRequest;
use App\Http\Requests\User\GetCountRequest;
use App\Http\Requests\User\GetListRequest;
use App\Http\Requests\User\EditPasswordRequest;
use App\Models\ClassModel;
use App\Utils\ResponseTool;

class UserController extends Controller
{

    // 添加学生
    public function add(AddRequest $request)
    {
        $student_list = $request->input("data");
        foreach ($student_list as $item) {
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
        $student_list = $request->input("student_list");

        foreach ($student_list as $item) {
            $request->user()->deleteByAccount($item);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }



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

        $all_user = [];
        foreach ($users as $item) {
            $userData = array(
                'name' => $item->name,
                'account' => $item->account,
                'class_id' => $item->class_id,
                'class_name' => ClassModel::getClassName($item->class_id),
            );
            array_push($all_user, $userData);
        }
        return ResponseTool::jsonResponse(0, 'success', $all_user);
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

    // 获取班级的学生数量
    public function getCount(GetCountRequest $request)
    {
        return ResponseTool::jsonResponse(0, 'success', $request->user()->getCountByClass($request->input("class_id"), $request->input("query_word")));
    }
}
