<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠詳細画面の名前がログインユーザーの氏名になっている
     */
    public function test_user_name_is_displayed(): void
    {
        [$user, $attendanceRecord] = $this->createAttendanceUser();

        $response = $this->get(
            '/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    /**
     * 勤怠詳細画面の日付が選択した日付になっている
     */
    public function test_attendance_date_is_displayed(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        $response = $this->get(
            '/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertStatus(200);
        $response->assertSee(
            date('Y', strtotime($attendanceRecord->date))
        );
        $response->assertSee(
            date('m/d', strtotime($attendanceRecord->date))
        );
    }

    /**
     * 出勤・退勤時間がログインユーザーの打刻と一致している
     */
    public function test_clock_in_and_out_times_are_displayed(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        $attendanceRecord->update([
            'clock_out' => '18:00:00',
        ]);

        $response = $this->get(
            '/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 休憩時間がログインユーザーの打刻と一致している
     */
    public function test_break_times_are_displayed(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $response = $this->get(
            '/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
