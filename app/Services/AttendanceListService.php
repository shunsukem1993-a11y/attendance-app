<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceListService
{
    /**
     * 指定月の勤怠記録を取得する。
     */
    public function getMonthlyRecords(
        User $user,
        Carbon $month
    ): Collection {
        $attendanceRecords = AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->get()
            ->keyBy(function (AttendanceRecord $attendanceRecord) {
                return $attendanceRecord->date;
            });

        $records = collect();

        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        for (
            $date = $startDate->copy();
            $date->lte($endDate);
            $date->addDay()
        ) {
            $dateString = $date->toDateString();

            $attendanceRecord = $attendanceRecords->get($dateString);

            $totalBreakTime = $this->calculateTotalBreakTime(
                $attendanceRecord
            );

            $totalWorkTime = $this->calculateTotalWorkTime(
                $attendanceRecord,
                $totalBreakTime
            );

            $records->push([
                'id' => $attendanceRecord?->id,
                'date' => $date->format('m/d'),
                'clock_in' => $attendanceRecord?->clock_in
                    ? Carbon::parse($attendanceRecord->clock_in)->format('H:i')
                    : '',
                'clock_out' => $attendanceRecord?->clock_out
                    ? Carbon::parse($attendanceRecord->clock_out)->format('H:i')
                    : '',
                'total_break_time' => $totalBreakTime,
                'total_time' => $totalWorkTime,
            ]);
        }

        return $records;
    }

    /**
     * 休憩時間の合計を計算する。
     */
    private function calculateTotalBreakTime(
        ?AttendanceRecord $attendanceRecord
    ): ?string {
        if (! $attendanceRecord) {
            return null;
        }

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
