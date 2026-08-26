<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、バリデーションエラーになる
     */
    public function test_email_is_required(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'admin_status' => false,
        ]);

        $response = $this->post('/login', [
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
            'admin_status' => false,
        ]);

        $response = $this->post('/login', [
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
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'admin_status' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

        $this->assertGuest();
    }

    /**
     * 正しい認証情報の場合、ログインできる
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'admin_status' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('attendance.create'));

        $this->assertAuthenticatedAs($user);
    }

    /**
     * 一般ユーザーがログアウトできる
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');

        $this->assertGuest();
    }

    /**
     * 未認証ユーザーは勤怠画面にアクセスできない
     */
    public function test_guest_cannot_access_attendance(): void
    {
        $response = $this->get('/attendance');

        $response->assertRedirect('/login');
    }

    /**
     * ログイン画面から会員登録画面へ遷移できる
     */
    public function test_login_page_has_register_link(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee(('/register'));
    }
}
