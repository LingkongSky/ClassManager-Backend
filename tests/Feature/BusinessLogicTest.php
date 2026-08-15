<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ClassModel;
use App\Models\Homework;
use App\Models\PermissionsGroup;
use App\Models\Student;
use App\Utils\NumericId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_numeric_ids_are_unique_twenty_digit_values(): void
    {
        $seen = [];
        $first = NumericId::generate(fn(string $id) => isset($seen[$id]));
        $seen[$first] = true;
        $second = NumericId::generate(fn(string $id) => isset($seen[$id]));

        $this->assertMatchesRegularExpression('/^\d{20}$/', $first);
        $this->assertMatchesRegularExpression('/^\d{20}$/', $second);
        $this->assertNotSame($first, $second);
    }

    public function test_student_creation_preserves_the_requested_primary_class(): void
    {
        ClassModel::create(['class_id' => '1001', 'class_name' => '一班', 'student_count' => 0]);
        ClassModel::create(['class_id' => '1002', 'class_name' => '二班', 'student_count' => 0]);
        $admin = Student::where('account', 'admin')->firstOrFail();

        $result = $admin->createStudent(
            'student-1',
            '学生甲',
            'password-hash',
            ['1001', '1002'],
            primaryClassId: '1002',
        );

        $this->assertTrue($result['success']);
        $this->assertSame('1002', Student::getUserClass('student-1'));
        $this->assertDatabaseHas('student_class_map', [
            'class_id' => '1002',
            'is_primary' => 1,
        ]);
    }

    public function test_deleting_a_class_removes_related_database_rows_and_files(): void
    {
        foreach (['publish', 'homework', 'temp', 'output'] as $disk) {
            Storage::fake($disk);
        }

        ClassModel::create(['class_id' => '2001', 'class_name' => '待删除班级', 'student_count' => 0]);
        $activityId = Activity::create(
            '作业',
            '标题',
            '2001',
            '要求',
            '[姓名]',
            '.docx',
            '2026-01-01 00:00:00',
            '2026-01-02 00:00:00',
        );
        Homework::submitHomework($activityId, 'admin', '2001', '作业.docx');
        DB::table('student_class_map')->insert([
            'student_id' => Student::where('account', 'admin')->value('id'),
            'class_id' => '2001',
            'is_primary' => 1,
            'created_at' => now(),
        ]);
        foreach (['publish', 'homework', 'temp', 'output'] as $disk) {
            Storage::disk($disk)->put("{$activityId}/file.txt", 'test');
        }

        $result = ClassModel::deleteClass('2001');

        $this->assertTrue($result['success']);
        $this->assertDatabaseMissing('class', ['class_id' => '2001']);
        $this->assertDatabaseMissing('activity', ['activity_id' => $activityId]);
        $this->assertDatabaseMissing('homework', ['activity_id' => $activityId]);
        $this->assertDatabaseMissing('student_class_map', ['class_id' => '2001']);
        foreach (['publish', 'homework', 'temp', 'output'] as $disk) {
            Storage::disk($disk)->assertMissing($activityId);
        }
    }

    public function test_an_in_use_permission_group_cannot_be_deleted(): void
    {
        ClassModel::create(['class_id' => '3001', 'class_name' => '权限班级', 'student_count' => 0]);
        PermissionsGroup::create(['group_id' => 'group-1', 'name' => '管理员组', 'target' => '3001']);
        Student::where('account', 'admin')->update(['permissions_group_id' => 'group-1']);

        $result = PermissionsGroup::deleteGroup('group-1');

        $this->assertFalse($result['success']);
        $this->assertDatabaseHas('permissions_group', ['group_id' => 'group-1']);
    }
}
