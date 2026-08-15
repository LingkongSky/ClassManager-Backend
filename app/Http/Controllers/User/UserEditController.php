<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddRequest;
use App\Http\Requests\User\DeleteRequest;
use App\Http\Requests\User\EditRequest;
use App\Http\Requests\User\EditPasswordRequest;
use App\Utils\ResponseTool;
use DomainException;
use Illuminate\Support\Facades\DB;

class UserEditController extends Controller
{

    // 添加学生
    public function add(AddRequest $request)
    {
        $studentList = $request->input("data");
        try {
            DB::transaction(function () use ($request, $studentList) {
                foreach ($studentList as $item) {
                    $result = $request->user()->createStudent(
                        $item['account'],
                        $item['name'],
                        $item['password'],
                        $item['class_ids'],
                        primaryClassId: $item['primary_class_id'],
                    );

                    if ($result === null) {
                        throw new DomainException('无权限创建该班级的学生', 403);
                    }
                    if (!($result['success'] ?? false)) {
                        throw new DomainException($result['message'] ?? '创建学生失败', 422);
                    }
                }
            });
        } catch (DomainException $exception) {
            return ResponseTool::jsonResponse(1, $exception->getMessage(), null, $exception->getCode());
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除学生
    public function delete(DeleteRequest $request)
    {
        $studentList = $request->input("student_list");

        try {
            DB::transaction(function () use ($request, $studentList) {
                foreach ($studentList as $item) {
                    $result = $request->user()->deleteByAccount($item);
                    if ($result === null) {
                        throw new DomainException('无权限：仅可管理主班级在权限组内的学生', 403);
                    }
                    if (!($result['success'] ?? false)) {
                        throw new DomainException($result['message'] ?? '删除学生失败', 422);
                    }
                }
            });
        } catch (DomainException $exception) {
            return ResponseTool::jsonResponse(2, $exception->getMessage(), null, $exception->getCode());
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
        if (!($result['success'] ?? false)) {
            return ResponseTool::jsonResponse(1, $result['message'] ?? '修改用户失败', null, 422);
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
        if (!($result['success'] ?? false)) {
            return ResponseTool::jsonResponse(1, $result['message'] ?? '修改密码失败', null, 422);
        }
        return ResponseTool::jsonResponse(0, 'success', null);
    }
}
