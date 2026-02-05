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
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'purpose_id')) {
                $table
                    ->foreignId('purpose_id')
                    ->default(1)  
                    ->after('created_by')
                    ->constrained('purposes')
                    ->cascadeOnDelete()
                    ->comment('カテゴリーID');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['purpose_id']); 
            $table->dropColumn('purpose_id');
        });
    }
};
