<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    public function run()
    {
        // User::factory()->count(30)->create();
        $count = 30; // 作成するユーザー数

        for ($i = 1; $i <= $count; $i++) {
            User::create([
                'name' => "TestUser{$i}",
                'email' => "testuser{$i}@example.com",
                'password' => Hash::make('password'), // パスワードは共通で設定
                'email_verified_at' => now(),  // メール認証済み
            ]);
        }
    }

}
