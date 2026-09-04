<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後に認証メールが送信される
     */
    public function test_verification_email_is_sent_after_registration(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    /**
     * 「認証はこちらから」ボタンからメール認証サイトへ遷移できる
     */
    public function test_verification_page_has_mailpit_link(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('verification.notice'));

        $response->assertStatus(200);

        $response->assertSee('認証はこちらから');

        $response->assertSee('http://localhost:8025');
    }

    /**
     * メール認証完了後に勤怠登録画面へ遷移する
     */
    public function test_user_is_redirected_to_attendance_after_email_verification(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this
            ->actingAs($user)
            ->get($verificationUrl);

        $response->assertRedirect(
            route('attendance.create').'?verified=1'
        );

        $this->assertNotNull(
            $user->fresh()->email_verified_at
        );
    }
}
