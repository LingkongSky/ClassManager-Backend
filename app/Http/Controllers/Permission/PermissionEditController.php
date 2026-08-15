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
use DomainException;
use Illuminate\Support\Facades\DB;

class PermissionEditController extends Controller
{

    // 创建管理员
    public function addAdmin(AddAdminRequest $request)
    {
        $data = $request->getFormattedData();
        try {
            DB::transaction(function () use ($request, $data) {
                foreach ($data as $item) {
                    $result = $request->user()->createStudent(
                        $item['account'],
                        $item['name'],
                        $item['password'],
                        $item['class_ids'] ?? [],
                        5,
                        $item['permissions_group_id'],
                        ($item['primary_class_id'] ?? '') ?: null,
                    );

                    if ($result === null) {
                        throw new DomainException('无权限创建管理员', 403);
                    }
                    if (!($result['success'] ?? false)) {
                        throw new DomainException($result['message'] ?? '创建管理员失败', 422);
                    }
                }
            });
        } catch (DomainException $exception) {
            return ResponseTool::jsonResponse(1, $exception->getMessage(), null, $exception->getCode());
        }
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除管理员
    public function deleteAdmin(DeleteAdminRequest $request)
    {

        $adminList = $request->input("admin_list");

        try {
            DB::transaction(function () use ($request, $adminList) {
                foreach ($adminList as $item) {
                    $result = $request->user()->deleteByAccount($item);
                    if (!($result['success'] ?? false)) {
                        throw new DomainException($result['message'] ?? '删除管理员失败', 422);
                    }
                }
            });
        } catch (DomainException $exception) {
            return ResponseTool::jsonResponse(1, $exception->getMessage(), null, $exception->getCode());
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 设置管理员
    public function setAdmin(SetAdminRequest $request)
    {
        $adminList = $request->input("admin_list");
        try {
            DB::transaction(function () use ($adminList) {
                foreach ($adminList as $item) {
                    $result = Student::setPermissionGroup($item['account'], $item["permissions_group_id"]);
                    if (!($result['success'] ?? false)) {
                        throw new DomainException($result['message'] ?? '设置权限组失败', 422);
                    }
                }
            });
        } catch (DomainException $exception) {
            return ResponseTool::jsonResponse(1, $exception->getMessage(), null, $exception->getCode());
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 修改管理员
    public function editAdmin(EditAdminRequest $request)
    {
        $result = Student::editAdmin(
            $request->input('account'),
            $request->input('name'),
            $request->input('class_ids', []),
            $request->input('permissions_group_id'),
            $request->input('primary_class_id')
        );
        if (!($result['success'] ?? false)) {
            return ResponseTool::jsonResponse(1, $result['message'] ?? '修改管理员失败', null, 422);
        }
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 修改管理员密码
    public function editAdminPassword(EditAdminPasswordRequest $request)
    {
        $result = Student::editAdminPassword($request->input('account'), $request->input('password'));
        if (!($result['success'] ?? false)) {
            return ResponseTool::jsonResponse(1, $result['message'] ?? '修改管理员密码失败', null, 422);
        }
        return ResponseTool::jsonResponse(0, 'success', null);
    }

}
