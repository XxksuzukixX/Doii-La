<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id(); // 予約ID
            $table->string('title', 255)->comment('募集タイトル');
            $table->string('staff_name', 255)->comment('担当者氏名');
            $table->text('description')->nullable()->comment('内容詳細');
            $table->dateTime('deadline_at')->nullable()->comment('締切日時');
            $table->timestamps();
            $table->softDeletes()->comment('削除日時');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};