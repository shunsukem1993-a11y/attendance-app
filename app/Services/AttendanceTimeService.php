<?php

namespace App\Services;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class AttendanceTimeService
{
    /**
     * 休憩時間の合計を計算する。
     */
    public function calculateTotalBreakTime(
        ?AttendanceRecord $attendanceRecord
    ): ?string {
        if (! $attendanceRecord) {
            return null;
        }

        $totalSeconds = $attendanceRecord->breaks
            ->filter(function (AttendanceBreak $break): bool {
                return $break->break_in && $break->break_out;
            })
            ->sum(function (AttendanceBreak $break): int {
                $breakIn = Carbon::parse($break->break_in);
                $breakOut = Carbon::parse($break->break_out);

                return $breakIn->diffInSeconds($breakOut);
            });

        return $totalSeconds > 0
            ? gmdate('H:i:s', $totalSeconds)
            : null;
    }

    /**
     * 実働時間を計算する。
     */
    public function calculateTotalWorkTime(
        ?AttendanceRecord $attendanceRecord,
        ?string $totalBreakTime
    ): ?string {
        if (
            ! $attendanceRecord ||
            ! $attendanceRecord->clock_in ||
            ! $attendanceRecord->clock_out
        ) {
            return null;
        }

        $clockIn = Carbon::parse($attendanceRecord->clock_in);
        $clockOut = Carbon::parse($attendanceRecord->clock_out);

        $workSeconds = $clockIn->diffInSeconds($clockOut);

        if ($totalBreakTime) {
            $breakSeconds = Carbon::parse($totalBreakTime)
                ->diffInSeconds(Carbon::parse('00:00:00'));

            $workSeconds -= $breakSeconds;
        }

        return gmdate('H:i:s', $workSeconds);
    }
}
