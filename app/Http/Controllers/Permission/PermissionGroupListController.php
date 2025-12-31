<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\PermissionsGroup;
use App\Models\ClassModel;
use App\Utils\ResponseTool;
use App\Http\Requests\Permission\GetCountRequest;
use App\Http\Requests\Permission\GetGroupRequest;

class PermissionGroupListController extends Controller
{
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
}
