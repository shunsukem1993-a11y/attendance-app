<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceReportAnomalyService
{
    /**
     * 今月の異常勤怠件数を取得する。
     *
     * 遅刻、早退、長時間労働の発生回数を集計する。
     *
     * @param  Collection<int, array<string, mixed>>  $dailyResults
     *                                                               1日ごとの計算結果のCollection
     * @return array<string, int> 異常勤怠件数
     */
    public function getAnomalies(
        Collection $dailyResults
    ): array {
        $currentMonth = now();

        $monthlyResults = $dailyResults->filter(
            fn (array $result): bool => Carbon::parse($result['record']->date)
                ->isSameMonth($currentMonth)
        );

        $lateCount = $monthlyResults
            ->filter(
                fn (array $result): bool => $result['record']->clock_in &&
                    Carbon::parse($result['record']->clock_in)
                        ->gt(Carbon::parse('09:00:00'))
            )
            ->count();

        $earlyLeaveCount = $monthlyResults
            ->filter(
                fn (array $result): bool => $result['record']->clock_out &&
                    Carbon::parse($result['record']->clock_out)
                        ->lt(Carbon::parse('18:00:00'))
            )
            ->count();

        $longWorkCount = $monthlyResults
            ->filter(
                fn (array $result): bool => $result['work_seconds'] !== null &&
                    $result['work_seconds'] > (10 * 3600)
            )
            ->count();

        return [
            'late_count' => $lateCount,
            'early_leave_count' => $earlyLeaveCount,
            'long_work_count' => $longWorkCount,
        ];
    }
}
