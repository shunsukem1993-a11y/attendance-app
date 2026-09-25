<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者の正しい認証情報の場合、ログインできる
     */
    public function test_admin_can_login(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'admin_status' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.attendance.list'));

        $this->assertAuthenticatedAs($admin);
    }

    /**
     * 管理者がログアウトできる
     */
    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/logout');

        $response->assertRedirect('/admin/login');

        $this->assertGuest();
    }

    /**
     * 未認証ユーザーは管理画面にアクセスできない
     */
    public function test_guest_cannot_access_admin_attendance(): void
    {
        $response = $this->get('/admin/attendance/list');

        $response->assertRedirect('/admin/login');
    }
}
