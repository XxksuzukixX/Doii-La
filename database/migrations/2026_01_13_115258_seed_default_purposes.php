<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('purposes')->insert([
            ['key' => 'meeting',    'name' => '会議',            'image_path' => 'kaigi_shinken_business_people.png', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'briefing',   'name' => '説明会',          'image_path' => 'setsumeikai_seminar.png',           'created_at' => now(), 'updated_at' => now()],
            ['key' => 'recreation', 'name' => 'レクリエーション', 'image_path' => 'kids_chanbara_kamifusen.png',       'created_at' => now(), 'updated_at' => now()],
            ['key' => 'interview',  'name' => '面接練習',        'image_path' => 'syukatsu_group_mensetsu.png',       'created_at' => now(), 'updated_at' => now()],
            ['key' => 'request',    'name' => '業務依頼',        'image_path' => 'businessman_workaholic_woman.png',  'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::table('purposes')->whereIn('key', ['meeting', 'briefing', 'recreation', 'interview'])->delete();
    }
};