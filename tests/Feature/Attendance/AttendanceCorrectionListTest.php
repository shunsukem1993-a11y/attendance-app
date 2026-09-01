<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 承認待ちに自分の申請が全て表示される
     */
    public function test_pending_requests_are_displayed(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        $this->post(
            '/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '10:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['13:00'],
                'comment' => '1件目の修正申請',
            ]
        )->assertRedirect();

        $attendanceRecord2 = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->subDay()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->post(
            '/attendance/detail/'.$attendanceRecord2->id,
            [
                'new_clock_in' => '11:00',
                'new_clock_out' => '19:00',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['13:00'],
                'comment' => '2件目の修正申請',
            ]
        )->assertRedirect();

        $response = $this->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('1件目の修正申請');
        $response->assertSee('2件目の修正申請');
    }

    /**
     * 管理者が承認した複数の申請が
     * 「承認済み」に全て表示される
     */
    public function test_approved_requests_are_displayed(): void
    {
        [$user, $attendanceRecord] = $this->createAttendanceUser();

        $this->post(
            '/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '10:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['13:00'],
                'comment' => '承認済み申請テスト1',
            ]
        )->assertRedirect();

        $this->post(
            '/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '11:00',
                'new_clock_out' => '19:00',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['13:00'],
                'comment' => '承認済み申請テスト2',
            ]
        )->assertRedirect();

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $this->actingAs($admin);

        $this->post(
            '/stamp_correction_request/approve/'.$request1->id
        )->assertRedirect();

        $this->post(
            '/stamp_correction_request/approve/'.$request2->id
        )->assertRedirect();

        $this->actingAs($user);

        $response = $this->get(
            '/stamp_correction_request/list?status=approved'
        );

        $response->assertStatus(200);

        $response->assertSee('承認済み申請テスト1');
        $response->assertSee('承認済み申請テスト2');

        $response->assertSee('10:00');
        $response->assertSee('18:00');
        $response->assertSee('11:00');
        $response->assertSee('19:00');
    }

    /**
     * 修正申請を作成する
     */
    private function createCorrectionRequest(
        User $user,
        AttendanceRecord $attendanceRecord,
        string $comment,
        string $clockIn,
        string $clockOut
    ): AttendanceCorrectionRequest {
        return AttendanceCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_record_id' => $attendanceRecord->id,
            'approval_status' => AttendanceCorrectionRequest::STATUS_PENDING,
            'comment' => $comment,
            'new_clock_in' => $clockIn,
            'new_clock_out' => $clockOut,
        ]);
    }
}
