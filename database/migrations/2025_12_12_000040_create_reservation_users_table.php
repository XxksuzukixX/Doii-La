<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_users', function (Blueprint $table) {
            $table->id(); // 予約申込ID

            $table->foreignId('slot_id')
                  ->comment('予約枠ID')
                  ->constrained('reservation_slots')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->comment('ユーザーID')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->string('status', 32)->comment('予約ステータス');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_users');
    }
};
