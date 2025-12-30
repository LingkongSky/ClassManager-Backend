<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\PermissionsGroup;
use App\Models\ClassModel;
use App\Utils\ResponseTool;
use App\Http\Requests\Permission\GetCountRequest;
use App\Http\Requests\Permission\GetGroupRequest;
use App\Http\Requests\Permission\AddGroupRequest;
use App\Http\Requests\Permission\DeleteGroupRequest;
use App\Http\Requests\Permission\EditGroupRequest;

class GroupController extends Controller
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

    // 查询权限组列表
    public function getList()
    {
        return ResponseTool::jsonResponse(0, 'success', PermissionsGroup::getAllGroups());
    }

    // 获取权限组数量
    public function getCount(GetCountRequest $request)
    {
        return ResponseTool::jsonResponse(0, 'success', PermissionsGroup::getGroupCount($request->input("query_word")));
    }

    // 获取权限组列表
    public function getGroupList(GetGroupRequest $request)
    {
        $queryCount = $request->input('query_count');
        $queryWord = $request->input('query_word');

        $allGroups = [];

        $groups = PermissionsGroup::getGroupList($queryCount, $queryWord);

        // 构建返回格式
        foreach ($groups as $group) {

            // 生成班级名称
            $classIdArray = explode("|", $group->target);
            $classList = "";
            foreach ($classIdArray as $classId) {
                $classList = $classList . " " . ClassModel::getClassName($classId);
            }

            $groupData = array(
                'name' => $group->name,
                'group_id' => $group->group_id,
                'class_id_list' => $group->target,
                'class_name_list' => $classList,
            );

            array_push($allGroups, $groupData);
        }
        return ResponseTool::jsonResponse(0, 'success', $allGroups);
    }


    public function editGroup(EditGroupRequest $request)
    {
        PermissionsGroup::editGroup($request->input('permissions_group_id'), $request->input('permissions_group_name'), $request->input('class_list'));
        return ResponseTool::jsonResponse(0, 'success', null);
    }
}