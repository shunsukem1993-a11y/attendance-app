<?php

namespace Tests\Feature;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 一般ユーザーと勤怠レコードを作成する。
     */
    private function createAttendanceRecord(): array
    {
        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '通常勤務',
        ]);

        return [$user, $attendanceRecord];
    }

    /**
     * 休憩情報を作成する。
     */
    private function createBreak(AttendanceRecord $attendanceRecord): void
    {
        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);
    }

    /**
     * 管理者が選択した勤怠詳細を確認できる
     */
    public function test_admin_can_see_selected_attendance_detail(): void
    {
        $this->createAdminUser();
        [$user, $attendanceRecord] = $this->createAttendanceRecord();

        $response = $this->get(
            '/admin/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee(now()->format('m/d'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('通常勤務');
    }

    /**
     * 出勤時間が退勤時間より後の場合、エラーになる
     */
    public function test_clock_in_after_clock_out_shows_error(): void
    {
        $this->createAdminUser();
        [, $attendanceRecord] = $this->createAttendanceRecord();

        $response = $this->get(
            '/admin/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertStatus(200);

        $response = $this->post(
            '/admin/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '19:00',
                'new_clock_out' => '18:00',
                'comment' => '勤務時間を修正',
            ]
        );

        $response->assertSessionHasErrors([
            'new_clock_in' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /**
     * 休憩開始時間が退勤時間より後の場合、エラーになる
     */
    public function test_break_in_after_clock_out_shows_error(): void
    {
        $this->createAdminUser();
        [, $attendanceRecord] = $this->createAttendanceRecord();

        $this->createBreak($attendanceRecord);

        $response = $this->get(
            '/admin/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertStatus(200);

        $response = $this->post(
            '/admin/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['19:00'],
                'new_break_out' => ['20:00'],
                'comment' => '休憩時間を修正',
            ]
        );

        $response->assertSessionHasErrors([
            'new_break_in.0' => '休憩時間が不適切な値です。',
        ]);
    }

    /**
     * 休憩終了時間が退勤時間より後の場合、エラーになる
     */
    public function test_break_out_after_clock_out_shows_error(): void
    {
        $this->createAdminUser();
        [, $attendanceRecord] = $this->createAttendanceRecord();

        $this->createBreak($attendanceRecord);

        $response = $this->get(
            '/admin/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertStatus(200);

        $response = $this->post(
            '/admin/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['19:00'],
                'comment' => '休憩時間を修正',
            ]
        );

        $response->assertSessionHasErrors([
            'new_break_out.0' => '休憩時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /**
     * 備考が未入力の場合、エラーになる
     */
    public function test_comment_required(): void
    {
        $this->createAdminUser();
        [, $attendanceRecord] = $this->createAttendanceRecord();

        $response = $this->get(
            '/admin/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertStatus(200);

        $response = $this->post(
            '/admin/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'comment' => '',
            ]
        );

        $response->assertSessionHasErrors([
            'comment' => '備考を記入してください。',
        ]);
    }

    /**
     * 管理者以外は管理者勤怠詳細にアクセスできない
     */
    public function test_general_user_cannot_access_admin_attendance_detail(): void
    {
        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(
            '/admin/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertForbidden();
    }
}
