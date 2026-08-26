<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、バリデーションエラーになる
     */
    public function test_email_is_required(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'admin_status' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /**
     * パスワードが未入力の場合、バリデーションエラーになる
     */
    public function test_password_is_required(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'admin_status' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * 登録内容と一致しない場合、ログインできない
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'admin_status' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

        $this->assertGuest();
    }

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
