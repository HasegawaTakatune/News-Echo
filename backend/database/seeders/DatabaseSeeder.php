<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 管理者ユーザー
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('password123'),
                'is_admin' => true,
            ]
        );

        // 一般ユーザー
        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'user',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ]
        );

        // 投稿用アカウント・リサーチプロンプトの初期データ
        Setting::updateOrCreate(
            ['key' => 'post_account'],
            ['value' => 'news_bot']
        );
        Setting::updateOrCreate(
            ['key' => 'research_prompt'],
            ['value' => '最新の情報を収集して、簡潔にまとめてください。']
        );

        $adminUser = User::where('name', '=', 'admin')->select('id')->first();
        $normalUser = User::where('name', '=', 'user')->select('id')->first();

        for ($i = 0; $i < 100; $i++) {
            News::updateOrCreate([
                'user_id' => $adminUser->id,
                'title' => Str::random(10),
                'post_interval' => '1month',
            ]);

            News::updateOrCreate([
                'user_id' => $normalUser->id,
                'title' => Str::random(10),
                'post_interval' => '1month',
            ]);
        }
    }
}
