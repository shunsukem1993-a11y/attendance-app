<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterEmailFormatTest extends TestCase
{
    use RefreshDatabase;

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
}
