<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_slots', function (Blueprint $table) {
            $table->id(); // 予約枠ID

            $table->foreignId('reservation_id')
                  ->comment('予約ID')
                  ->constrained('reservations')
                  ->cascadeOnDelete();

            $table->integer('capacity')->comment('定員');
            $table->integer('current_count')->comment('現在の予約人数');
            $table->dateTime('start_at')->nullable()->comment('開始日時');
            $table->dateTime('end_at')->nullable()->comment('終了日時');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_slots');
    }
};
