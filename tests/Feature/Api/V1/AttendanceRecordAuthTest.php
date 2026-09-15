<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceRecordAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証ユーザーが勤怠登録APIを利用できないことを確認する。
     */
    public function test_unauthenticated_user_cannot_create_attendance_record(): void
    {
        $data = [
            'date' => '2026-09-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '未認証テスト',
        ];

        $response = $this->postJson(
            '/api/v1/attendance-records',
            $data
        );

        $response
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * 未認証ユーザーが勤怠更新APIを利用できないことを確認する。
     */
    public function test_unauthenticated_user_cannot_update_attendance_record(): void
    {
        $data = [
            'date' => '2026-09-16',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'comment' => '未認証更新テスト',
        ];

        $response = $this->putJson(
            '/api/v1/attendance-records/99999',
            $data
        );

        $response
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * 未認証ユーザーが勤怠削除APIを利用できないことを確認する。
     */
    public function test_unauthenticated_user_cannot_delete_attendance_record(): void
    {
        $response = $this->deleteJson(
            '/api/v1/attendance-records/99999'
        );

        $response
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * 認証済みユーザーが自身の勤怠を更新できることを確認する。
     */
    public function test_authenticated_user_can_update_own_attendance_record(): void
    {
        $user = User::factory()->create();

        $attendanceRecord = $user->attendanceRecords()->create([
            'date' => '2026-09-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '更新前',
        ]);

        $data = [
            'date' => '2026-09-16',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'comment' => '自分の勤怠を更新',
        ];

        Sanctum::actingAs($user);

        $response = $this->putJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            $data
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.id', $attendanceRecord->id);
    }

    /**
     * 認証済みユーザーが自身の勤怠を削除できることを確認する。
     */
    public function test_authenticated_user_can_delete_own_attendance_record(): void
    {
        $user = User::factory()->create();

        $attendanceRecord = $user->attendanceRecords()->create([
            'date' => '2026-09-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '削除テスト',
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}"
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing('attendance_records', [
            'id' => $attendanceRecord->id,
        ]);
    }

    /**
     * 一般ユーザーが他ユーザーの勤怠を更新できないことを確認する。
     */
    public function test_user_cannot_update_another_users_attendance_record(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $attendanceRecord = $otherUser->attendanceRecords()->create([
            'date' => '2026-09-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '他ユーザーの勤怠',
        ]);

        Sanctum::actingAs($user);

        $data = [
            'date' => '2026-09-16',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'comment' => '不正な更新',
        ];

        $response = $this->putJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            $data
        );

        $response
            ->assertStatus(403)
            ->assertJson([
                'error' => 'この操作を実行する権限がありません。',
            ]);
    }

    /**
     * 一般ユーザーが他ユーザーの勤怠を削除できないことを確認する。
     */
    public function test_user_cannot_delete_another_users_attendance_record(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $attendanceRecord = $otherUser->attendanceRecords()->create([
            'date' => '2026-09-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '他ユーザーの勤怠',
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}"
        );

        $response
            ->assertStatus(403)
            ->assertJson([
                'error' => 'この操作を実行する権限がありません。',
            ]);
    }
}
