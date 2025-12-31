<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\AddAdminRequest;
use App\Http\Requests\Permission\DeleteAdminRequest;
use App\Http\Requests\Permission\SetAdminRequest;
use App\Http\Requests\Permission\EditAdminRequest;
use App\Http\Requests\Permission\EditAdminPasswordRequest;
use App\Models\Student;
use App\Utils\ResponseTool;

class PermissionEditController extends Controller
{

    // 创建管理员
    public function addAdmin(AddAdminRequest $request)
    {
        $data = $request->getFormattedData();
        foreach ($data as $item) {
            $request->user()->createStudent(
                $item['account'],
                $item['name'],
                $item['password'],
                $item['class_id'],
                5,
                $item['permissions_group_id'],
            );
        }
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除管理员
    public function deleteAdmin(DeleteAdminRequest $request)
    {

        $adminList = $request->input("admin_list");

        foreach ($adminList as $item) {
            $request->user()->deleteByAccount($item);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 设置管理员
    public function setAdmin(SetAdminRequest $request)
    {
        $adminList = $request->input("admin_list");
        foreach ($adminList as $item) {
            Student::setPermissionGroup($item['account'], $item["permissions_group_id"]);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 修改管理员
    public function editAdmin(EditAdminRequest $request)
    {
        Student::editAdmin($request->input('account'), $request->input('name'), $request->input('class_id'), $request->input('permissions_group_id'));
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 修改管理员密码
    public function editAdminPassword(EditAdminPasswordRequest $request)
    {
        Student::editAdminPassword($request->input('account'), $request->input('password'));
        return ResponseTool::jsonResponse(0, 'success', null);
    }

}
