<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRecordNotFoundTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 存在しない勤怠情報の更新で404が返ることを確認する。
     */
    public function test_update_nonexistent_attendance_record_returns_404(): void
    {
        $user = User::factory()->create();

        $data = [
            'date' => '2026-09-15',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'comment' => '更新テスト',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/attendance-records/99999', $data);

        $response
            ->assertStatus(404)
            ->assertJson([
                'error' => '勤怠情報が見つかりませんでした。',
            ]);
    }

    /**
     * 存在しない勤怠情報の削除で404が返ることを確認する。
     */
    public function test_delete_nonexistent_attendance_record_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/attendance-records/99999');

        $response
            ->assertStatus(404)
            ->assertJson([
                'error' => '勤怠情報が見つかりませんでした。',
            ]);
    }
}
