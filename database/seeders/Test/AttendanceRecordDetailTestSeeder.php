<?php

namespace Database\Seeders\Test;

use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceRecordDetailTestSeeder extends Seeder
{
    /**
     * 勤怠詳細APIテスト用のデータを作成する。
     */
    public function run(): void
    {
        $user = User::where('email', 'user1@example.com')
            ->firstOrFail();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-09-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '詳細APIテスト用勤務',
        ]);

        AttendanceBreak::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        AttendanceCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_record_id' => $attendanceRecord->id,
            'approval_status' => 0,
            'comment' => '詳細APIテスト用修正申請',
            'new_date' => '2026-09-01',
            'new_clock_in' => '09:30:00',
            'new_clock_out' => '18:00:00',
        ]);
    }
}
