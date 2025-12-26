<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_mailbox', function (Blueprint $table) {
            $table->id();
            $table->string('account', 20)->nullable(false);
            $table->text('unread_mails')->nullable(false);
            $table->text('read_mails')->nullable(false);
            $table->text('all_mails')->nullable(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_mailbox');
    }
};