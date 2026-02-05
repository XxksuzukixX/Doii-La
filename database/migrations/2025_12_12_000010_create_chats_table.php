<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id(); // チャットID
            $table->foreignId('sender_id')
                  ->comment('送信者ID')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('receiver_id')
                  ->comment('受信者ID')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('message', 255)->comment('メッセージ');
            $table->tinyInteger('read_flg')->default(0)->comment('既読フラグ');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};