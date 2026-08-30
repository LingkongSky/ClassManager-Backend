<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ApiExceptionResponseTest extends TestCase
{
    public function test_api_exception_message_is_returned_in_debug_mode(): void
    {
        config(['app.debug' => true]);
        Route::get('/api/_test/model-error', function () {
            throw new RuntimeException('模型保存失败: database unavailable');
        });

        $this->getJson('/api/_test/model-error')
            ->assertStatus(500)
            ->assertExactJson([
                'code' => 1,
                'message' => '模型保存失败: database unavailable',
                'data' => null,
            ]);
    }

    public function test_api_exception_details_are_hidden_in_production_mode(): void
    {
        config(['app.debug' => false]);
        Route::get('/api/_test/production-error', function () {
            throw new RuntimeException('sensitive database detail');
        });

        $this->getJson('/api/_test/production-error')
            ->assertStatus(500)
            ->assertExactJson([
                'code' => 1,
                'message' => '服务器内部错误，请稍后重试',
                'data' => null,
            ]);
    }
}
