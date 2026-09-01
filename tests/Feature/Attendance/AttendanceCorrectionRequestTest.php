<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 修正申請処理が実行され、
     * 管理者の承認画面に申請内容が表示される
     */
    public function test_correction_request_is_created(): void
    {
        [$user, $attendanceRecord] = $this->createAttendanceUser();

        $response = $this->post(
            '/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '10:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['13:00'],
                'comment' => '出勤時間を修正します',
            ]
        );

        $response->assertRedirect();

        $request = AttendanceCorrectionRequest::where(
            'attendance_record_id',
            $attendanceRecord->id
        )->firstOrFail();

        $this->assertDatabaseHas(
            'attendance_correction_requests',
            [
                'user_id' => $user->id,
                'attendance_record_id' => $attendanceRecord->id,
                'approval_status' => AttendanceCorrectionRequest::STATUS_PENDING,
                'comment' => '出勤時間を修正します',
                'new_clock_in' => '10:00',
                'new_clock_out' => '18:00',
            ]
        );

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->get(
            '/stamp_correction_request/approve/'.$request->id
        );

        $response->assertStatus(200);
        $response->assertSee('出勤時間を修正します');
        $response->assertSee('10:00');
        $response->assertSee('18:00');
    }

    /**
     * 申請の詳細から勤怠詳細画面へ遷移できる
     */
    public function test_can_navigate_to_attendance_detail_from_request(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        $response = $this->post(
            '/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '10:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['13:00'],
                'comment' => '詳細画面遷移テスト',
            ]
        );

        $response->assertRedirect();

        $response = $this->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('詳細画面遷移テスト');

        $response->assertSee(
            '/attendance/detail/'.$attendanceRecord->id
        );

        $response = $this->get(
            '/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }
}
