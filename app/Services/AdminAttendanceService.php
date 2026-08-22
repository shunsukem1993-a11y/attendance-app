<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminAttendanceService
{
    /**
     * 全ユーザーを取得する。
     */
    public function getUsers(): Collection
    {
        return User::all();
    }

    /**
     * 指定日の勤怠記録を取得する。
     */
    public function getDailyRecords(Carbon $date): Collection
    {
        $attendanceRecords = AttendanceRecord::with('breaks')
            ->whereDate('date', $date)
            ->get();

        foreach ($attendanceRecords as $attendanceRecord) {
            $totalBreakTime = $this->calculateTotalBreakTime(
                $attendanceRecord
            );

            $totalWorkTime = $this->calculateTotalWorkTime(
                $attendanceRecord,
                $totalBreakTime
            );

            $attendanceRecord->total_break_time = $totalBreakTime;
            $attendanceRecord->total_time = $totalWorkTime;
        }

        return $attendanceRecords;
    }

    /**
     * 休憩時間の合計を計算する。
     */
    private function calculateTotalBreakTime(
        AttendanceRecord $attendanceRecord
    ): ?string {
        $totalSeconds = 0;

        foreach ($attendanceRecord->breaks as $break) {
            if (! $break->break_in || ! $break->break_out) {
                continue;
            }

            $breakIn = Carbon::parse($break->break_in);
            $breakOut = Carbon::parse($break->break_out);

            $totalSeconds += $breakIn->diffInSeconds($breakOut);
        }

        return $totalSeconds > 0
            ? gmdate('H:i:s', $totalSeconds)
            : null;
    }

    /**
     * 実働時間を計算する。
     */
    private function calculateTotalWorkTime(
        AttendanceRecord $attendanceRecord,
        ?string $totalBreakTime
    ): ?string {
        if (
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
