<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterPageTest extends TestCase
{
    use RefreshDatabase;

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
