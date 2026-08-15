<?php

namespace App\Http\Controllers\Class;

use App\Http\Controllers\Controller;
use App\Http\Requests\Class\AddRequest;
use App\Http\Requests\Class\AppendStudentsRequest;
use App\Http\Requests\Class\DeleteRequest;
use App\Http\Requests\Class\EditRequest;
use App\Models\ClassModel;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use App\Utils\ResponseTool;
use App\Utils\NumericId;
use DomainException;

class ClassEditController extends Controller
{
    // 创建班级
    public function add(AddRequest $request)
    {
        $classList = $request->input("class_list");
        try {
            DB::transaction(function () use ($classList) {
                foreach ($classList as $item) {
                    $classId = NumericId::generate(fn(string $id) => ClassModel::where('class_id', $id)->exists());
                    $result = ClassModel::addClass($classId, $item['class_name']);
                    if (is_array($result) && !($result['success'] ?? false)) {
                        throw new DomainException($result['message'] ?? '创建班级失败', 422);
                    }
                }
            });
        } catch (DomainException $exception) {
            return ResponseTool::jsonResponse(1, $exception->getMessage(), null, $exception->getCode());
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除班级
    public function delete(DeleteRequest $request)
    {
        $classList = $request->input("class_list");

        try {
            DB::transaction(function () use ($classList) {
                foreach (array_unique($classList) as $item) {
                    $result = ClassModel::deleteClass($item);
                    if (!($result['success'] ?? false)) {
                        throw new DomainException($result['message'] ?? '删除班级失败', 422);
                    }
                }
            });
        } catch (DomainException $exception) {
            return ResponseTool::jsonResponse(1, $exception->getMessage(), null, $exception->getCode());
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 编辑班级
    public function edit(EditRequest $request)
    {
        $classId = $request->input('class_id');
        $className = $request->input('class_name');
        $result = ClassModel::editClass($classId, $className);
        if (!($result['success'] ?? false)) {
            return ResponseTool::jsonResponse(1, $result['message'] ?? '修改班级失败', null, 422);
        }
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 快速建班：仅追加副班级，不改主班级
    public function appendStudents(AppendStudentsRequest $request)
    {
        $targetClassId = $request->input('target_class_id');
        $accounts = $request->input('student_accounts');

        if (!ClassModel::where('class_id', $targetClassId)->exists()) {
            return ResponseTool::jsonResponse(1, '目标班级不存在', null, 422);
        }

        if (empty($accounts)) {
            return ResponseTool::jsonResponse(1, '学生列表不能为空', null, 422);
        }

        $students = Student::query()
            ->whereIn('account', $accounts)
            ->select(['id', 'account'])
            ->get();

        $studentIdMap = [];
        foreach ($students as $student) {
            $studentIdMap[$student->account] = $student->id;
        }

        $notFoundAccounts = array_values(array_filter($accounts, fn($account) => !isset($studentIdMap[$account])));
        if (!empty($notFoundAccounts)) {
            return ResponseTool::jsonResponse(1, '存在无效学生账号', ['not_found_accounts' => $notFoundAccounts], 422);
        }

        DB::transaction(function () use ($accounts, $studentIdMap, $targetClassId) {
            $studentIds = array_values(array_map(fn($account) => $studentIdMap[$account], $accounts));

            $existsStudentIds = DB::table('student_class_map')
                ->where('class_id', $targetClassId)
                ->whereIn('student_id', $studentIds)
                ->pluck('student_id')
                ->toArray();
            $existsMap = array_flip($existsStudentIds);

            $now = now();
            $insertRows = [];
            foreach ($studentIds as $studentId) {
                if (isset($existsMap[$studentId])) {
                    continue;
                }
                $insertRows[] = [
                    'student_id' => $studentId,
                    'class_id' => $targetClassId,
                    'is_primary' => 0,
                    'created_at' => $now,
                ];
            }

            if (!empty($insertRows)) {
                DB::table('student_class_map')->insert($insertRows);
            }
        });

        ClassModel::updateClassStatus();
        return ResponseTool::jsonResponse(0, 'success', null);
    }
}
