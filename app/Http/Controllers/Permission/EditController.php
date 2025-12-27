<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Class\AddRequest;
use App\Http\Requests\Class\DeleteRequest;
use App\Http\Requests\Permission\AddAdminRequest;
use App\Http\Requests\Permission\DeleteAdminRequest;
use App\Http\Requests\Permission\SetAdminRequest;
use App\Http\Requests\Permission\GetAdminRequest;
use App\Http\Requests\Permission\GetCountAdminRequest;
use App\Http\Requests\Permission\EditAdminRequest;
use App\Http\Requests\Permission\EditAdminPasswordRequest;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\PermissionsGroup;
use App\Utils\ResponseTool;

class EditController extends Controller
{
    // 创建班级
    public function add(AddRequest $request)
    {
        $class_list = $request->input("class_list");
        $i = 0;

        foreach ($class_list as $item) {
            $time = time() . $i;
            ClassModel::addClass($time, $item["class_name"]);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除班级
    public function delete(DeleteRequest $request)
    {
        $class_list = $request->input("class_list");

        foreach ($class_list as $item) {
            ClassModel::deleteClass($item);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 创建管理员
    public function addAdmin(AddAdminRequest $request)
    {
        $data = $request->getFormattedData();
        foreach ($data as $item) {
            Student::createStudent(
                $item['account'],
                $item['name'],
                $item['password'],
                5,
                $item['class_id'],
                $item['permissions_group_id'],
            );
        }
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除管理员
    public function deleteAdmin(DeleteAdminRequest $request)
    {

        $admin_list = $request->input("admin_list");

        foreach ($admin_list as $item) {
            Student::deleteByAccount($item);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 查询管理员
    public function getAdminList(GetAdminRequest $request)
    {
        $queryCount = $request->input('query_count');
        $queryWord = $request->input('query_word');

        $all_students = [];

        $students = Student::getAdminList($queryCount, $queryWord);

        // 构建返回格式
        foreach ($students as $student) {
            $studentsData = array(
                'account' => $student->account,
                'name' => $student->name,
                'class_id' => $student->class_id,
                'permissions_group_id' => $student->permissions_group_id,
                'permissions_group_name' => PermissionsGroup::getGroupName($student->permissions_group_id),
                'level' => $student->level,
            );
            array_push($all_students, $studentsData);
        }

        return ResponseTool::jsonResponse(0, 'success', $all_students);
    }

    // 设置管理员
    public function setAdmin(SetAdminRequest $request)
    {
        $admin_list = $request->input("admin_list");
        foreach ($admin_list as $item) {
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

    // 获取管理员数量
    public function getCount(GetCountAdminRequest $request)
    {
        return ResponseTool::jsonResponse(0, 'success', Student::getAdminCount($request->input("query_word")));
    }
}
