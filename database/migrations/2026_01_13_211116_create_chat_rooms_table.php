<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();

            $table->string('type')->comment('private / group');
            $table->string('name')->nullable()->comment('グループ名（1対1はnull）');
            $table->foreignId('created_by')
                ->comment('作成者')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('last_message_at')->nullable()->comment('最終メッセージ日時');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_rooms');
    }
};
