<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceListService
{
    private AttendanceTimeService $attendanceTimeService;

    public function __construct(AttendanceTimeService $attendanceTimeService)
    {
        $this->attendanceTimeService = $attendanceTimeService;
    }

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

            $totalBreakTime = $this->attendanceTimeService
                ->calculateTotalBreakTime($attendanceRecord);

            $totalWorkTime = $this->attendanceTimeService
                ->calculateTotalWorkTime(
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
}
