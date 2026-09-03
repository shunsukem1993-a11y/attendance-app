<?php

namespace Tests\Feature;

use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\ProposalBreak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CorrectionRequestTestHelper;
use Tests\TestCase;

class AdminAttendanceCorrectionRequestTest extends TestCase
{
    use CorrectionRequestTestHelper;
    use RefreshDatabase;

    /**
     * 管理者が申請詳細を確認できる
     */
    public function test_application_detail_is_displayed(): void
    {
        $this->createAdminUser();

        $user = User::factory()->create([
            'name' => '申請者ユーザー',
        ]);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-09-01',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $application = $this->createCorrectionRequest(
            $user,
            $attendanceRecord,
            AttendanceCorrectionRequest::STATUS_PENDING,
            [
                'comment' => '勤務時間を修正',
                'new_date' => '2026-09-01',
                'new_clock_in' => '09:30',
                'new_clock_out' => '18:30',
            ]
        );

        $response = $this->get(
            "/stamp_correction_request/approve/{$application->id}"
        );

        $response->assertOk();
        $response->assertViewIs('admin.admin-application-detail');

        $response->assertViewHas('application', function ($value) use ($application) {
            return $value->id === $application->id;
        });

        $response->assertViewHas('user', function ($value) use ($user) {
            return $value->id === $user->id;
        });

        $response->assertSee('申請者ユーザー');
        $response->assertSee('勤務時間を修正');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
    }

    /**
     * 管理者が申請を承認すると勤怠情報が更新される
     */
    public function test_application_can_be_approved(): void
    {
        $this->createAdminUser();

        $user = User::factory()->create();

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-09-01',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'comment' => '修正前コメント',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:00',
            'break_out' => '13:00',
        ]);

        $application = $this->createCorrectionRequest(
            $user,
            $attendanceRecord,
            AttendanceCorrectionRequest::STATUS_PENDING,
            [
                'comment' => '修正後コメント',
                'new_date' => '2026-09-02',
                'new_clock_in' => '09:30',
                'new_clock_out' => '18:30',
            ]
        );

        ProposalBreak::factory()->create([
            'attendance_correction_request_id' => $application->id,
            'break_in' => '12:30',
            'break_out' => '13:30',
        ]);

        $response = $this->post(
            "/stamp_correction_request/approve/{$application->id}"
        );

        $response->assertRedirect(
            route(
                'admin.attendance.correction.show',
                $application->id
            )
        );

        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $application->id,
            'approval_status' => AttendanceCorrectionRequest::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendanceRecord->id,
            'date' => '2026-09-02 00:00:00',
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'comment' => '修正後コメント',
        ]);

        $this->assertDatabaseHas('breaks', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:30',
            'break_out' => '13:30',
        ]);

        $this->assertDatabaseMissing('breaks', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:00',
            'break_out' => '13:00',
        ]);
    }
}
