<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('chat_room_users', function (Blueprint $table) {
            $table->unsignedBigInteger('last_notified_chat_id')
                  ->nullable()
                  ->after('last_notified_at');

            // 任意：外部キーを張るなら
            // $table->foreign('last_notified_chat_id')
            //       ->references('id')
            //       ->on('chats')
            //       ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chat_room_users', function (Blueprint $table) {
            // $table->dropForeign(['last_notified_chat_id']);
            $table->dropColumn('last_notified_chat_id');
        });
    }
};
