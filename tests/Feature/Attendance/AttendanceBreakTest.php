<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 休憩ボタンが正しく機能する
     */
    public function test_user_can_start_break(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');

        $this->assertDatabaseHas('breaks', [
            'attendance_record_id' => $attendanceRecord->id,
        ]);
    }

    /**
     * 休憩は一日に何回でもできる
     */
    public function test_user_can_take_multiple_breaks(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        // 1回目の休憩
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        $this->post('/attendance', [
            'action' => 'break_out',
        ]);

        // 2回目の休憩
        $response = $this->get('/attendance');

        $response->assertSee('休憩入');

        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');

        $this->assertCount(
            2,
            AttendanceBreak::where(
                'attendance_record_id',
                $attendanceRecord->id
            )->get()
        );
    }

    /**
     * 休憩戻ボタンが正しく機能する
     */
    public function test_user_can_end_break(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        // 休憩開始
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        // 休憩終了
        $this->post('/attendance', [
            'action' => 'break_out',
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');

        $this->assertDatabaseMissing('breaks', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_out' => null,
        ]);
    }

    /**
     * 休憩戻は一日に何回でもできる
     */
    public function test_user_can_end_multiple_breaks(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        // 1回目の休憩
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        $this->post('/attendance', [
            'action' => 'break_out',
        ]);

        // 2回目の休憩
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        $this->post('/attendance', [
            'action' => 'break_out',
        ]);

        $this->assertCount(
            2,
            AttendanceBreak::where(
                'attendance_record_id',
                $attendanceRecord->id
            )->get()
        );
    }

    /**
     * 休憩時刻が勤怠一覧画面で確認できる
     */
    public function test_break_time_is_recorded(): void
    {
        $this->travelTo(now()->setTime(12, 0));

        [, $attendanceRecord] = $this->createAttendanceUser();

        // 12:00に休憩開始
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        // 13:00に休憩終了
        $this->travelTo(now()->setTime(13, 0));

        $this->post('/attendance', [
            'action' => 'break_out',
        ]);

        // DBに休憩時間が保存されていることを確認
        $break = AttendanceBreak::where(
            'attendance_record_id',
            $attendanceRecord->id
        )->first();

        $this->assertNotNull($break);
        $this->assertNotNull($break->break_in);
        $this->assertNotNull($break->break_out);

        $this->assertEquals('12:00:00', $break->break_in);
        $this->assertEquals('13:00:00', $break->break_out);

        // 勤怠一覧画面を確認
        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

        // 休憩1時間が「1:00」と表示される
        $response->assertSee('1:00');

        $this->travelBack();
    }
}
