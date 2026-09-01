<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤ボタンが正しく機能する
     */
    public function test_user_can_clock_in(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');

        $response->assertStatus(200);

        // 「出勤」ボタンが表示されていることを確認
        $response->assertSee('出勤');

        // 出勤処理を行う
        $response = $this->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $response->assertRedirect('/attendance');

        // 出勤後の勤怠打刻画面を開く
        $response = $this->get('/attendance');

        $response->assertStatus(200);

        // ステータスが「出勤中」になっていることを確認
        $response->assertSee('出勤中');

        // 勤怠記録が保存されていることを確認
        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'date' => now()->toDateString(),
        ]);
    }

    /**
     * 出勤は一日一回のみできる
     */
    public function test_user_can_clock_in_only_once_per_day(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        $response = $this->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $response->assertRedirect('/attendance');

        $response->assertSessionHas(
            'error',
            '本日はすでに出勤済みです。'
        );

        $this->assertDatabaseCount('attendance_records', 1);
    }

    /**
     * 出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_in_time_is_recorded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $attendanceRecord = AttendanceRecord::where(
            'user_id',
            $user->id
        )->first();

        $this->assertNotNull($attendanceRecord);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(
            date('H:i', strtotime($attendanceRecord->clock_in))
        );
    }

    /**
     * 退勤ボタンが正しく機能する
     */
    public function test_user_can_clock_out(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');

        $response->assertStatus(200);

        // 「退勤」ボタンが表示されていることを確認
        $response->assertSee('退勤');

        // 退勤処理を行う
        $response = $this->post('/attendance', [
            'action' => 'clock_out',
        ]);

        $response->assertRedirect('/attendance');

        // 退勤後の勤怠打刻画面を開く
        $response = $this->get('/attendance');

        $response->assertStatus(200);

        // ステータスが「退勤済」になっていることを確認
        $response->assertSee('退勤済');

        $attendanceRecord->refresh();

        $this->assertNotNull($attendanceRecord->clock_out);
    }

    /**
     * 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_out_time_is_recorded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // 出勤処理
        $this->post('/attendance', [
            'action' => 'clock_in',
        ]);

        // 退勤処理
        $this->post('/attendance', [
            'action' => 'clock_out',
        ]);

        // 勤怠一覧画面を表示
        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

        // 退勤時刻が表示されていることを確認
        $attendanceRecord = AttendanceRecord::where(
            'user_id',
            $user->id
        )->first();

        $response->assertSee(
            date('H:i', strtotime($attendanceRecord->clock_out))
        );
    }
}
