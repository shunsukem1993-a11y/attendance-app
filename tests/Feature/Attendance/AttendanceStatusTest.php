<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceBreak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 現在の日時情報がUIと同じ形式で表示される
     */
    public function test_current_datetime_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y年m月d日'));
        $response->assertSee(now()->format('H:i'));
    }

    /**
     * 勤務外の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_is_off_duty(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /**
     * 出勤中の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_is_working(): void
    {
        $this->createAttendanceUser();

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * 休憩中の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_is_on_break(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:00:00',
            'break_out' => null,
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * 退勤済の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_is_finished(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        $attendanceRecord->update([
            'clock_out' => '18:00:00',
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
