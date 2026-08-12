<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ユーザー１（一般）を作成
        User::create([
            'name' => '一般ユーザー1',
            'email' => 'user1@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
            'admin_status' => false,
        ]);

        // ユーザー２（一般）を作成
        User::create([
            'name' => '一般ユーザー2',
            'email' => 'user2@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
            'admin_status' => false,
        ]);

        // ユーザー３（管理者）を作成
        User::create([
            'name' => '管理者ユーザー',
            'email' => 'user3@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
            'admin_status' => true,
        ]);
    }
}
