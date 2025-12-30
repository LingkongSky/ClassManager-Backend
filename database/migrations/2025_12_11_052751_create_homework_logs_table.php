<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_logs', function (Blueprint $table) {
            $table->id();
            $table->string('account', 20)->nullable(false);
            $table->string('file_name', 348)->nullable(false);
            $table->string('class_id', 20)->nullable(false);
            $table->string('activity_id', 20)->nullable(false);
            $table->string('event', 100)->nullable(false);
            $table->string('update_time', 99)->nullable(false);
            $table->string('ip', 40)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_logs');
    }
};