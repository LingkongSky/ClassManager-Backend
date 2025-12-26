<?php
// database/seeders/StudentSeeder.php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // 创建管理员账户
        Student::create([
            'name' => '管理员',
            'password' => '4d939a02ca918650678e62036995d2154c5dae0f46a2305b456922bd34cb9c57',
            'account' => 'admin',
            'class_id' => null,
            'level' => 10,
            'permissions_group_id' => '1',
        ]);

    }
}