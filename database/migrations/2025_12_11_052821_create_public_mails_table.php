<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_mails', function (Blueprint $table) {
            $table->id();
            $table->string('mail_id', 20)->unique()->nullable(false);
            $table->text('title')->nullable(false);
            $table->string('owner', 20)->nullable(false);
            $table->text('content')->nullable(false);
            $table->text('target')->nullable(false);
            $table->string('readtimes', 10)->nullable(false);
            $table->string('type', 10)->nullable(false);
            $table->string('start_time', 99)->nullable(false);
            $table->string('stop_time', 99)->nullable(false);
            $table->string('status', 10)->nullable(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_mails');
    }
};