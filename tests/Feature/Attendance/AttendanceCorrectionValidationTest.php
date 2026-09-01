<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤時間が退勤時間より後の場合、
     * エラーメッセージが表示される
     */
    public function test_clock_in_after_clock_out_shows_error(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        $response = $this->post(
            '/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '19:00',
                'new_clock_out' => '18:00',
                'comment' => '修正申請',
            ]
        );

        $response->assertSessionHasErrors([
            'new_clock_in' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /**
     * 休憩開始時間が退勤時間より後の場合、
     * エラーメッセージが表示される
     */
    public function test_break_in_after_clock_out_shows_error(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        $response = $this->post(
            '/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['19:00'],
                'new_break_out' => ['19:30'],
                'comment' => '休憩時間を修正',
            ]
        );

        $response->assertSessionHas(
            'errors',
            fn ($errors) => $errors->has('new_break_in.0')
                && $errors->first('new_break_in.0')
                    === '休憩時間が不適切な値です。'
        );
    }

    /**
     * 休憩終了時間が退勤時間より後の場合、
     * エラーメッセージが表示される
     */
    public function test_break_out_after_clock_out_shows_error(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        $response = $this->post(
            '/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['17:00'],
                'new_break_out' => ['19:00'],
                'comment' => '休憩時間を修正',
            ]
        );

        $response->assertSessionHas(
            'errors',
            fn ($errors) => $errors->has('new_break_out.0')
                && $errors->first('new_break_out.0')
                    === '休憩時間もしくは退勤時間が不適切な値です。'
        );
    }

    /**
     * 備考欄が未入力の場合、
     * エラーメッセージが表示される
     */
    public function test_comment_required(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        $response = $this->post(
            '/attendance/detail/'.$attendanceRecord->id,
            [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'new_break_in' => [''],
                'new_break_out' => [''],
                'comment' => '',
            ]
        );

        $response->assertSessionHasErrors([
            'comment' => '備考を記入してください。',
        ]);
    }
}
