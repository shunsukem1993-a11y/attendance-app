<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者が全一般ユーザーの氏名を確認できる
     */
    public function test_admin_can_see_all_general_users_names(): void
    {
        $this->createAdminUser();

        $user1 = User::factory()->create([
            'name' => '一般ユーザー1',
            'email' => 'user1@example.com',
            'admin_status' => false,
        ]);

        $user2 = User::factory()->create([
            'name' => '一般ユーザー2',
            'email' => 'user2@example.com',
            'admin_status' => false,
        ]);

        $response = $this->get('/admin/staff/list');

        $response->assertStatus(200);

        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
    }

    /**
     * 管理者が全一般ユーザーのメールアドレスを確認できる
     */
    public function test_admin_can_see_all_general_users_emails(): void
    {
        $this->createAdminUser();

        $user1 = User::factory()->create([
            'name' => '一般ユーザー1',
            'email' => 'user1@example.com',
            'admin_status' => false,
        ]);

        $user2 = User::factory()->create([
            'name' => '一般ユーザー2',
            'email' => 'user2@example.com',
            'admin_status' => false,
        ]);

        $response = $this->get('/admin/staff/list');

        $response->assertStatus(200);

        $response->assertSee($user1->email);
        $response->assertSee($user2->email);
    }
}
