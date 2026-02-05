<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reservation_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('対象の予約ID');

            $table->foreignId('editor_id')
                ->constrained('users')
                ->comment('編集者ユーザーID');

            $table->string('action', 50)
                ->comment('操作種別（create, update など）');

            $table->text('comment')
                ->nullable()
                ->comment('編集時コメント');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_histories');
    }
};
