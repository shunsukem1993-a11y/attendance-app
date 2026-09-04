<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 名前が未入力の場合、バリデーションエラーになる
     */
    public function test_name_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /**
     * メールアドレスが未入力の場合、バリデーションエラーになる
     */
    public function test_email_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /**
     * パスワードが8文字未満の場合、バリデーションエラーになる
     */
    public function test_password_must_be_at_least_8_characters(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /**
     * パスワードと確認用パスワードが一致しない場合、バリデーションエラーになる
     */
    public function test_password_confirmation_must_match(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    /**
     * パスワードが未入力の場合、バリデーションエラーになる
     */
    public function test_password_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * メールアドレスがメール形式ではない場合、バリデーションエラーになる
     */
    public function test_email_must_be_valid_format(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスはメール形式で入力してください',
        ]);
    }

    /**
     * 正しい入力の場合、ユーザーが正常に登録される
     */
    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'admin_status' => false,
        ]);

        $this->assertAuthenticated();
    }

    /**
     * 会員登録画面からログイン画面へ遷移できる
     */
    public function test_register_page_has_login_link(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('ログイン');
        $response->assertSee('/login');
    }
}
