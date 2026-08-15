<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\PermissionsGroup;
use App\Utils\ResponseTool;
use App\Http\Requests\Permission\AddGroupRequest;
use App\Http\Requests\Permission\DeleteGroupRequest;
use App\Http\Requests\Permission\EditGroupRequest;
use App\Utils\NumericId;
use DomainException;
use Illuminate\Support\Facades\DB;

class PermissionGroupEditController extends Controller
{

    // 创建权限组
    public function addGroup(AddGroupRequest $request)
    {
        $permissionsGroupList = $request->input("permissions_group_list");
        try {
            DB::transaction(function () use ($permissionsGroupList) {
                foreach ($permissionsGroupList as $item) {
                    $groupId = NumericId::generate(fn(string $id) => PermissionsGroup::where('group_id', $id)->exists());
                    $result = PermissionsGroup::addGroup($groupId, $item['name'], $item['target']);
                    if (is_array($result) && !($result['success'] ?? false)) {
                        throw new DomainException($result['message'] ?? '创建权限组失败', 422);
                    }
                }
            });
        } catch (DomainException $exception) {
            return ResponseTool::jsonResponse(1, $exception->getMessage(), null, $exception->getCode());
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除权限组
    public function deleteGroup(DeleteGroupRequest $request)
    {
        $permissionsGroupList = $request->input("permissions_group_list");

        try {
            DB::transaction(function () use ($permissionsGroupList) {
                foreach (array_unique($permissionsGroupList) as $item) {
                    $result = PermissionsGroup::deleteGroup($item);
                    if (!($result['success'] ?? false)) {
                        throw new DomainException($result['message'] ?? '删除权限组失败', 422);
                    }
                }
            });
        } catch (DomainException $exception) {
            return ResponseTool::jsonResponse(1, $exception->getMessage(), null, $exception->getCode());
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 编辑权限组
    public function editGroup(EditGroupRequest $request)
    {
        $result = PermissionsGroup::editGroup($request->input('permissions_group_id'), $request->input('permissions_group_name'), $request->input('class_list'));
        if (!($result['success'] ?? false)) {
            return ResponseTool::jsonResponse(1, $result['message'] ?? '修改权限组失败', null, 422);
        }
        return ResponseTool::jsonResponse(0, 'success', null);
    }
}
