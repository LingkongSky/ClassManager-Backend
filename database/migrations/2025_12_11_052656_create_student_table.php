<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student', function (Blueprint $table) {
            $table->id();
            $table->string('name', 10)->nullable(false);
            $table->string('password', 99)->nullable(false);
            $table->string('account', 20)->unique()->nullable(false);
            $table->string('date', 40)->nullable();
            $table->integer('level')->nullable(false);
            $table->string('class_id', 20)->nullable();
            $table->string('permissions_group_id', 20)->nullable();
        });

        DB::table('student')->insert([
            [
                'name' => 'Admin',
                'account' => 'admin',
                'level' => '10',
                'password' => "56ebb331c780d56f30076c304c4da48b6b4ddb5fce27ee7ae0de0806a9435f90", // Admin123
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('student');
    }
};