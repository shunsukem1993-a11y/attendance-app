<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRecordWriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_record_can_be_created(): void
    {
        $user = User::factory()->create();

        $data = [
            'date' => '2026-09-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'APIテスト',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/attendance-records', $data);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.date', '2026-09-15');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'date' => '2026-09-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'APIテスト',
        ]);
    }

    public function test_attendance_record_validation_error_returns_422(): void
    {
        $user = User::factory()->create();

        $data = [
            'date' => '2026-09-15',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/attendance-records', $data);

        $response
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ])
            ->assertJsonPath(
                'errors.clock_in.0',
                '出勤時刻は必須です。'
            );
    }

    public function test_attendance_record_can_be_updated(): void
    {
        $user = User::factory()->create();

        $attendanceRecord = $user->attendanceRecords()->create([
            'date' => '2026-09-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '更新前',
        ]);

        $data = [
            'date' => '2026-09-15',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'comment' => '更新後',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->putJson(
                "/api/v1/attendance-records/{$attendanceRecord->id}",
                $data
            );

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.id', $attendanceRecord->id)
            ->assertJsonPath('data.clock_in', '10:00:00')
            ->assertJsonPath('data.clock_out', '19:00:00')
            ->assertJsonPath('data.comment', '更新後');

        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendanceRecord->id,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'comment' => '更新後',
        ]);
    }

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

    public function test_attendance_record_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $attendanceRecord = $user->attendanceRecords()->create([
            'date' => '2026-09-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '削除テスト',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson(
                "/api/v1/attendance-records/{$attendanceRecord->id}"
            );

        $response->assertStatus(204);

        $this->assertDatabaseMissing('attendance_records', [
            'id' => $attendanceRecord->id,
        ]);
    }

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
