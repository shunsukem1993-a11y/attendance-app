<?php

namespace Tests\Feature\Api\V1;

use App\Models\AttendanceRecord;
use App\Models\User;
use Database\Seeders\Test\AttendanceRecordDetailTestSeeder;
use Database\Seeders\Test\AttendanceRecordListTestSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRecordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠一覧を取得できることを確認する。
     */
    public function test_attendance_records_can_be_listed(): void
    {
        $this->seed([
            UserSeeder::class,
            AttendanceRecordListTestSeeder::class,
        ]);

        $user = User::where(
            'email',
            'user1@example.com'
        )->firstOrFail();

        $record = AttendanceRecord::where(
            'user_id',
            $user->id
        )
            ->latest('date')
            ->firstOrFail();

        $response = $this->getJson(
            '/api/v1/attendance-records'
        );

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $record->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'user_name',
                        'date',
                        'clock_in',
                        'clock_out',
                        'total_time',
                        'total_break_time',
                        'comment',
                    ],
                ],
                'links',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    /**
     * 指定された勤怠の詳細を取得できることを確認する。
     */
    public function test_attendance_record_detail_can_be_retrieved(): void
    {
        $this->seed([
            UserSeeder::class,
            AttendanceRecordDetailTestSeeder::class,
        ]);

        $attendanceRecord = AttendanceRecord::where(
            'comment',
            '詳細APIテスト用勤務'
        )->firstOrFail();

        $response = $this->getJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}"
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user' => [
                        'id',
                        'name',
                    ],
                    'date',
                    'clock_in',
                    'clock_out',
                    'breaks',
                    'applications',
                    'comment',
                ],
            ]);
    }

    /**
     * 存在しない勤怠を取得した場合に404が返ることを確認する。
     */
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
