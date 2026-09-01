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

    /**
     * 指定ユーザーの月次勤怠記録を取得する。
     */
    public function getStaffMonthlyRecords(
        User $user,
        Carbon $date
    ): Collection {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $attendanceRecords = AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->get()
            ->keyBy(function ($attendanceRecord) {
                return Carbon::parse($attendanceRecord->date)
                    ->format('Y-m-d');
            });

        $formattedRecords = collect();

        for (
            $currentDate = $startOfMonth->copy();
            $currentDate->lte($endOfMonth);
            $currentDate->addDay()
        ) {
            $attendanceRecord = $attendanceRecords->get(
                $currentDate->format('Y-m-d')
            );

            if ($attendanceRecord) {
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

            $formattedRecords->push([
                'id' => $attendanceRecord?->id,
                'date' => $currentDate->format('m/d'),
                'clock_in' => $attendanceRecord?->clock_in
                    ? Carbon::parse($attendanceRecord->clock_in)->format('H:i')
                    : '',
                'clock_out' => $attendanceRecord?->clock_out
                    ? Carbon::parse($attendanceRecord->clock_out)->format('H:i')
                    : '',
                'total_break_time' => $attendanceRecord?->total_break_time,
                'total_time' => $attendanceRecord?->total_time,
            ]);
        }

        return $formattedRecords;
    }
}
