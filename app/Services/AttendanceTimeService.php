<?php

namespace App\Services;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class AttendanceTimeService
{
    /**
     * 休憩時間の合計を計算する。
     *
     * @param  AttendanceRecord|null  $attendanceRecord  対象の勤怠情報
     * @return string|null 休憩時間の合計。休憩時間がない場合はnull
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
     *
     * @param  AttendanceRecord|null  $attendanceRecord  対象の勤怠情報
     * @param  string|null  $totalBreakTime  合計休憩時間
     * @return string|null 実働時間。出勤時刻または退勤時刻がない場合はnull
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

    /**
     * 勤怠の実働時間と休憩時間を計算する。
     *
     * @param  AttendanceRecord  $attendanceRecord  対象の勤怠情報
     * @return AttendanceRecord 計算結果を設定した勤怠情報
     */
    public function calculateAttendanceTimes(
        AttendanceRecord $attendanceRecord
    ): AttendanceRecord {
        $totalBreakTime = $this->calculateTotalBreakTime($attendanceRecord);

        $totalTime = $this->calculateTotalWorkTime(
            $attendanceRecord,
            $totalBreakTime
        );

        $attendanceRecord->setAttribute(
            'total_break_time',
            $totalBreakTime
        );

        $attendanceRecord->setAttribute(
            'total_time',
            $totalTime
        );

        $attendanceRecord->unsetRelation('breaks');

        return $attendanceRecord;
    }
}
