<?php

namespace Tests\Feature;

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

    /**
     * 管理者はスタッフ一覧に表示されない
     */
    public function test_admin_is_not_displayed_in_staff_list(): void
    {
        $admin = $this->createAdminUser();

        $user = User::factory()->create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'admin_status' => false,
        ]);

        $response = $this->get('/admin/staff/list');

        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee($user->email);

        $response->assertDontSee($admin->name);
        $response->assertDontSee($admin->email);
    }

    /**
     * 一般ユーザーは管理者スタッフ一覧にアクセスできない
     */
    public function test_general_user_cannot_access_admin_staff_list(): void
    {
        User::factory()->create([
            'admin_status' => false,
        ]);

        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get('/admin/staff/list');

        $response->assertForbidden();
    }
}
