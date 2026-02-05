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
        Schema::create('chat_room_users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('room_id')
                ->constrained('chat_rooms')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('role')->default('member')->comment('owner / admin / member');

            $table->foreignId('last_read_chat_id')
                ->nullable()
                ->constrained('chats')
                ->nullOnDelete();

            $table->timestamp('last_read_at')->nullable();
            
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();

            $table->unique(['room_id', 'user_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_room_users');
    }
};
