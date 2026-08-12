<?php

namespace Database\Seeders;

use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use Illuminate\Database\Seeder;

class AttendanceCorrectionRequestSeeder extends Seeder
{
    public function run(): void
    {
        $attendanceRecords = AttendanceRecord::inRandomOrder()
            ->limit(21)
            ->get();

        $requests = [
            // 出勤時間の修正
            [
                'comment' => '出勤時間を修正したい',
                'new_clock_in' => '09:30:00',
                'new_clock_out' => null,
            ],

            // 退勤時間の修正
            [
                'comment' => '退勤時間を修正したい',
                'new_clock_in' => null,
                'new_clock_out' => '19:00:00',
            ],

            // 休憩時間の修正
            [
                'comment' => '休憩時間を修正したい',
                'new_clock_in' => null,
                'new_clock_out' => null,
            ],

            // 出勤・退勤両方の修正
            [
                'comment' => '出勤・退勤時間を修正したい',
                'new_clock_in' => '09:30:00',
                'new_clock_out' => '19:00:00',
            ],

            // 日付の修正
            [
                'comment' => '勤務日を修正したい',
                'new_clock_in' => null,
                'new_clock_out' => null,
            ],

            // 出勤＋休憩
            [
                'comment' => '出勤時間と休憩時間を修正したい',
                'new_clock_in' => '09:30:00',
                'new_clock_out' => null,
            ],

            // 退勤＋休憩
            [
                'comment' => '退勤時間と休憩時間を修正したい',
                'new_clock_in' => null,
                'new_clock_out' => '19:00:00',
            ],
        ];

        foreach ($requests as $requestIndex => $request) {
            for ($status = 0; $status <= 2; $status++) {
                $attendanceRecord = $attendanceRecords[$requestIndex * 3 + $status];

                AttendanceCorrectionRequest::create([
                    'user_id' => $attendanceRecord->user_id,
                    'attendance_record_id' => $attendanceRecord->id,
                    'approval_status' => $status,
                    'comment' => $request['comment'],
                    'new_date' => $requestIndex === 4
                        ? date('Y-m-d', strtotime($attendanceRecord->date.' +1 day'))
                        : $attendanceRecord->date,
                    'new_clock_in' => $request['new_clock_in']
                        ?? $attendanceRecord->clock_in,
                    'new_clock_out' => $request['new_clock_out']
                        ?? $attendanceRecord->clock_out,
                ]);
            }
        }
    }
}
