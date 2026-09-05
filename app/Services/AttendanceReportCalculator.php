<?php

namespace App\Services;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class AttendanceReportCalculator
{
    /**
     * 1日の勤怠時間を計算する。
     *
     * 実働時間と残業時間をまとめて計算する。
     *
     * @param  AttendanceRecord  $attendanceRecord  勤怠記録
     * @return array<string, int|null> 1日の勤怠計算結果
     */
    public function calculate(
        AttendanceRecord $attendanceRecord
    ): array {
        $workSeconds = $this->calculateWorkSeconds($attendanceRecord);

        if ($workSeconds === null) {
            return [
                'work_seconds' => null,
                'overtime_seconds' => null,
            ];
        }

        return [
            'work_seconds' => $workSeconds,
            'overtime_seconds' => max(
                $workSeconds - (8 * 3600),
                0
            ),
        ];
    }

    /**
     * 1日の実働時間を秒数で計算する。
     *
     * 退勤時刻から出勤時刻を引き、
     * 完了した休憩時間の合計を差し引いて算出する。
     *
     * @param  AttendanceRecord  $attendanceRecord  勤怠記録
     * @return int|null 実働時間（秒）
     */
    private function calculateWorkSeconds(
        AttendanceRecord $attendanceRecord
    ): ?int {
        if (
            ! $attendanceRecord->clock_in ||
            ! $attendanceRecord->clock_out
        ) {
            return null;
        }

        $clockIn = Carbon::parse($attendanceRecord->clock_in);
        $clockOut = Carbon::parse($attendanceRecord->clock_out);

        $workSeconds = $clockIn->diffInSeconds($clockOut);

        $breakSeconds = $attendanceRecord->breaks
            ->filter(function (AttendanceBreak $break): bool {
                return $break->break_in && $break->break_out;
            })
            ->sum(function (AttendanceBreak $break): int {
                $breakIn = Carbon::parse($break->break_in);
                $breakOut = Carbon::parse($break->break_out);

                return $breakIn->diffInSeconds($breakOut);
            });

        return $workSeconds - $breakSeconds;
    }
}
