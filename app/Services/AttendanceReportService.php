<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceReportService
{
    public function __construct(
        private AttendanceReportCalculator $calculator,
    ) {}

    /**
     * 今月と今月を除く過去6か月分の勤怠記録を取得する。
     *
     * @param  User  $user  対象ユーザー
     * @return Collection<int, AttendanceRecord> 勤怠記録のCollection
     */
    public function getAttendanceRecords(User $user): Collection
    {
        $startDate = now()
            ->startOfMonth()
            ->subMonths(6);

        $endDate = now()->endOfMonth();

        return AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->get();
    }

    /**
     * 勤怠記録から1日ごとの計算結果を作成する。
     *
     * @param  Collection<int, AttendanceRecord>  $attendanceRecords
     *                                                                勤怠記録のCollection
     * @return Collection<int, array<string, mixed>> 1日ごとの計算結果
     */
    public function calculateDailyResults(
        Collection $attendanceRecords
    ): Collection {
        return $attendanceRecords->map(
            function (AttendanceRecord $attendanceRecord): array {
                return [
                    'record' => $attendanceRecord,
                    ...$this->calculator->calculate($attendanceRecord),
                ];
            }
        );
    }

    /**
     * 基本サマリーを取得する。
     *
     * 総労働時間、総残業時間、平均労働時間を計算する。
     *
     * @param  Collection<int, array<string, mixed>>  $dailyResults
     *                                                               1日ごとの計算結果のCollection
     * @return array<string, int> 基本サマリー
     */
    public function getSummary(
        Collection $dailyResults
    ): array {
        $startDate = now()
            ->startOfMonth()
            ->subMonths(6);

        $endDate = now()
            ->startOfMonth()
            ->subDay();

        $targetResults = $dailyResults->filter(
            fn (array $result): bool => Carbon::parse($result['record']->date)->between(
                $startDate,
                $endDate
            )
        );

        $workSeconds = $targetResults
            ->filter(
                fn (array $result): bool => $result['work_seconds'] !== null
            )
            ->sum('work_seconds');

        $overtimeSeconds = $targetResults
            ->filter(
                fn (array $result): bool => $result['overtime_seconds'] !== null
            )
            ->sum('overtime_seconds');

        $workDays = $targetResults
            ->filter(
                fn (array $result): bool => $result['work_seconds'] !== null
            )
            ->count();

        $averageWorkSeconds = $workDays > 0
            ? intdiv($workSeconds, $workDays)
            : 0;

        return [
            'total_work_minutes' => intdiv($workSeconds, 60),
            'total_overtime_minutes' => intdiv($overtimeSeconds, 60),
            'avg_work_minutes' => intdiv($averageWorkSeconds, 60),
        ];
    }

    /**
     * 過去6か月分の月次勤怠サマリーを取得する。
     *
     * 勤怠記録が存在しない月も0時間として返す。
     *
     * @param  Collection<int, array<string, mixed>>  $dailyResults
     *                                                               日次勤怠結果のCollection
     * @return Collection<int, array<string, int|string>> 月次勤怠サマリー
     */
    public function getMonthlySummary(
        Collection $dailyResults
    ): Collection {
        $startMonth = now()
            ->startOfMonth()
            ->subMonths(6);

        return collect(range(0, 5))->map(
            function (int $month) use (
                $startMonth,
                $dailyResults
            ): array {
                $targetMonth = $startMonth->copy()->addMonths($month);

                $monthlyResults = $dailyResults->filter(
                    fn (array $result): bool => Carbon::parse($result['record']->date)
                        ->isSameMonth($targetMonth)
                );

                $workSeconds = $monthlyResults
                    ->filter(
                        fn (array $result): bool => $result['work_seconds'] !== null
                    )
                    ->sum('work_seconds');

                $overtimeSeconds = $monthlyResults
                    ->filter(
                        fn (array $result): bool => $result['overtime_seconds'] !== null
                    )
                    ->sum('overtime_seconds');

                return [
                    'month' => $targetMonth->format('Y-m'),
                    'work_minutes' => intdiv($workSeconds, 60),
                    'overtime_minutes' => intdiv(
                        $overtimeSeconds,
                        60
                    ),
                ];
            }
        );
    }
}
