<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\PermissionsGroup;
use App\Utils\ResponseTool;
use App\Http\Requests\Permission\AddGroupRequest;
use App\Http\Requests\Permission\DeleteGroupRequest;
use App\Http\Requests\Permission\EditGroupRequest;

class PermissionGroupEditController extends Controller
{

    // 创建权限组
    public function addGroup(AddGroupRequest $request)
    {
        $permissionsGroupList = $request->input("permissions_group_list");
        $i = 0;

        foreach ($permissionsGroupList as $item) {
            $time = time() . $i;
            PermissionsGroup::addGroup($time, $item["name"], $item["target"]);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除权限组
    public function deleteGroup(DeleteGroupRequest $request)
    {
        $permissionsGroupList = $request->input("permissions_group_list");

        foreach ($permissionsGroupList as $item) {
            PermissionsGroup::deleteGroup($item);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 编辑权限组
    public function editGroup(EditGroupRequest $request)
    {
        PermissionsGroup::editGroup($request->input('permissions_group_id'), $request->input('permissions_group_name'), $request->input('class_list'));
        return ResponseTool::jsonResponse(0, 'success', null);
    }
}
