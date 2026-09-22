<?php

namespace Tests\Feature;

use App\Http\Requests\Request;
use App\Models\Activity;
use App\Models\ClassModel;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_upload_limit_matches_frontend_200_mb(): void
    {
        $environment = Env::getRepository();
        $original = $environment->get('FileMaxUplaodSize');
        $environment->clear('FileMaxUplaodSize');

        try {
            $config = require config_path('app.php');
            $this->assertSame(204800, $config['max_upload_size']);
        } finally {
            if ($original !== null) {
                $environment->set('FileMaxUplaodSize', $original);
            }
        }
    }

    public static function uploadCases(): iterable
    {
        foreach (['todos/submit', 'todos/submitAdmin', 'activity/create', 'activity/edit'] as $endpoint) {
            yield "$endpoint above old limit" => [$endpoint, 2048, true];
            yield "$endpoint at limit" => [$endpoint, 204800, true];
            yield "$endpoint over limit" => [$endpoint, 204801, false];
        }
    }

    #[DataProvider('uploadCases')]
    public function test_upload_endpoints_enforce_file_size_in_kb(string $endpoint, int $sizeKb, bool $accepted): void
    {
        config(['app.max_upload_size' => 204800]);
        Storage::fake('homework');
        Storage::fake('publish');
        ClassModel::create(['class_id' => '9001', 'class_name' => '上传班级', 'student_count' => 1]);
        $user = Student::where('account', 'admin')->firstOrFail();
        if ($endpoint === 'todos/submit') {
            $user->update(['level' => 0]);
        }
        DB::table('student_class_map')->insert([
            'student_id' => $user->id,
            'class_id' => '9001',
            'is_primary' => 1,
            'created_at' => now(),
        ]);
        $startTime = now()->subDay()->format('Y-m-d H:i:s');
        $stopTime = now()->addDay()->format('Y-m-d H:i:s');
        $activityId = Activity::create('原作业', '标题', '9001', '要求', '[姓名]', '.txt', $startTime, $stopTime);

        // Exercise real 2 MB storage; use reported sizes for the 200 MB boundary.
        $file = $sizeKb === 2048
            ? UploadedFile::fake()->createWithContent('homework.txt', str_repeat('a', $sizeKb * 1024))
            : UploadedFile::fake()->create('homework.txt', $sizeKb, 'text/plain');
        $isHomework = str_starts_with($endpoint, 'todos/');
        $payload = $isHomework
            ? ['activity_id' => $activityId, 'target_account' => $user->account, 'file' => $file]
            : [
                'activity_id' => $activityId,
                'class_id' => '9001',
                'name' => '新作业',
                'title' => '标题',
                'requestment' => '要求',
                'rename_format' => '[姓名]',
                'accept' => '.txt',
                'start_time' => $startTime,
                'stop_time' => $stopTime,
                'file_upload' => 'true',
                'files' => [$file],
            ];
        $response = $this->withToken($user->createToken('upload-test')->plainTextToken)
            ->post('/api/'.$endpoint, $payload, ['Accept' => 'application/json']);

        if (! $accepted) {
            $field = $isHomework ? 'file' : 'files.0';
            $response->assertStatus(422)->assertJsonPath('code', 1);
            $errors = $response->json('data.errors');
            $this->assertArrayHasKey($field, $errors);
            $this->assertStringContainsString('204800 KB', $errors[$field][0]);
            $this->assertStringNotContainsString('字符', $response->json('message'));
            $this->assertSame([], Storage::disk($isHomework ? 'homework' : 'publish')->allFiles());

            return;
        }

        $response->assertOk()->assertJsonPath('code', 0);
        if ($isHomework) {
            $this->assertDatabaseHas('homework', ['activity_id' => $activityId, 'account' => $user->account]);
        }
        $paths = Storage::disk($isHomework ? 'homework' : 'publish')->allFiles();
        $this->assertCount(1, $paths);
        if ($sizeKb === 2048) {
            $this->assertSame($sizeKb * 1024, Storage::disk($isHomework ? 'homework' : 'publish')->size($paths[0]));
        }
    }

    public function test_custom_file_limit_uses_size_message_and_text_keeps_character_message(): void
    {
        $validator = Validator::make([
            'file' => UploadedFile::fake()->create('homework.txt', 1025),
            'title' => '四个汉字',
        ], [
            'file' => 'file|max:1024',
            'title' => 'string|max:3',
        ], (new Request)->messages());

        $this->assertSame('file 文件大小不能超过 1024 KB', $validator->errors()->first('file'));
        $this->assertSame('title 不能超过3个字符', $validator->errors()->first('title'));
    }
}
