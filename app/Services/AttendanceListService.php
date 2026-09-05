<?php

namespace App\Services;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceListService
{
    /**
     * 指定月の勤怠記録を取得する。
     *
     * @param  User  $user  対象ユーザー
     * @param  Carbon  $month  対象月
     * @return Collection<int, array<string, mixed>> 月次勤怠データのCollection
     */
    public function getMonthlyRecords(
        User $user,
        Carbon $month
    ): Collection {
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        $attendanceRecords = AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->get()
            ->keyBy(function (AttendanceRecord $attendanceRecord): string {
                return $attendanceRecord->date;
            });

        return collect(
            $startDate->daysUntil($endDate)
        )->map(function (Carbon $date) use ($attendanceRecords): array {
            $dateString = $date->toDateString();

            $attendanceRecord = $attendanceRecords->get($dateString);

            $totalBreakTime = $this->calculateTotalBreakTime(
                $attendanceRecord
            );

            $totalWorkTime = $this->calculateTotalWorkTime(
                $attendanceRecord,
                $totalBreakTime
            );

            return [
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
            ];
        });
    }

    /**
     * 休憩時間の合計を計算する。
     *
     * @param  AttendanceRecord|null  $attendanceRecord  勤怠記録
     * @return string|null 休憩時間の合計
     */
    private function calculateTotalBreakTime(
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
     * @param  AttendanceRecord|null  $attendanceRecord  勤怠記録
     * @param  string|null  $totalBreakTime  合計休憩時間
     * @return string|null 実働時間
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
