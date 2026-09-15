<?php

namespace Tests\Feature\Api\V1;

use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_records_can_be_listed(): void
    {
        $user = User::factory()->create();

        $records = AttendanceRecord::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson(
            '/api/v1/attendance-records'
        );

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $records[0]->id,
                'user_id' => $user->id,
            ])
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_attendance_record_detail_can_be_retrieved(): void
    {
        $user = User::factory()->create();

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
        ]);

        AttendanceCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_record_id' => $attendanceRecord->id,
        ]);

        $response = $this->getJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}"
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'user_name',
                    'date',
                    'clock_in',
                    'clock_out',
                    'total_time',
                    'total_break_time',
                    'breaks',
                    'applications',
                    'comment',
                ],
            ]);
    }

    public function test_nonexistent_attendance_record_returns_404(): void
    {
        $response = $this->getJson(
            '/api/v1/attendance-records/99999'
        );

        $response->assertStatus(404)
            ->assertJson([
                'error' => '勤怠情報が見つかりませんでした。',
            ]);
    }
}
