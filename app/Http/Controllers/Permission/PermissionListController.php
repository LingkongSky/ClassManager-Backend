<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\GetAdminRequest;
use App\Http\Requests\Permission\GetCountAdminRequest;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\PermissionsGroup;
use App\Utils\ResponseTool;

class PermissionListController extends Controller
{
    // 查询管理员
    public function getAdminList(GetAdminRequest $request)
    {
        $queryCount = $request->input('query_count');
        $queryWord = $request->input('query_word');

        $allStudents = [];

        $students = Student::getAdminList($queryCount, $queryWord);

        // 构建返回格式
        foreach ($students as $student) {
            $classIds = Student::getUserClasses($student->account);
            $primaryClassId = $classIds[0] ?? null;
            $classNames = array_map(fn($id) => ClassModel::getClassName($id), $classIds);
            $studentsData = array(
                'account' => $student->account,
                'name' => $student->name,
                'class_id' => $primaryClassId,
                'class_name' => $primaryClassId ? ClassModel::getClassName($primaryClassId) : null,
                'class_ids' => $classIds,
                'class_names' => $classNames,
                'primary_class_id' => $primaryClassId,
                'primary_class_name' => $primaryClassId ? ClassModel::getClassName($primaryClassId) : null,
                'permissions_group_id' => $student->permissions_group_id,
                'permissions_group_name' => PermissionsGroup::getGroupName($student->permissions_group_id),
                'level' => $student->level,
            );
            array_push($allStudents, $studentsData);
        }

        return ResponseTool::jsonResponse(0, 'success', $allStudents);
    }

    // 获取管理员数量
    public function getCount(GetCountAdminRequest $request)
    {
        return ResponseTool::jsonResponse(0, 'success', Student::getAdminCount($request->input("query_word")));
    }
}
