<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminAttendanceCsvService
{
    /**
     * CSVファイルを生成する。
     *
     * @param  Collection<int, array<string, mixed>>  $attendanceRecords
     * @return string CSVデータ
     */
    public function generate(
        Collection $attendanceRecords
    ): string {
        $handle = fopen('php://memory', 'w+');

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            '日付',
            '出勤',
            '退勤',
            '休憩',
            '合計',
        ]);

        $attendanceRecords->each(
            function (array $attendanceRecord) use ($handle): void {
                fputcsv($handle, [
                    $attendanceRecord['date'],
                    $attendanceRecord['clock_in'],
                    $attendanceRecord['clock_out'],
                    $this->formatTime(
                        $attendanceRecord['total_break_time']
                    ),
                    $this->formatTime(
                        $attendanceRecord['total_time']
                    ),
                ]);
            }
        );

        rewind($handle);

        $csv = stream_get_contents($handle);

        fclose($handle);

        return $csv;
    }

    /**
     * 時間をCSV表示用に整形する。
     *
     * @param  string|null  $time  時間
     * @return string 整形された時間
     */
    private function formatTime(?string $time): string
    {
        return $time
            ? Carbon::parse($time)->format('G:i')
            : '';
    }

    /**
     * CSVファイル名を生成する。
     *
     * @param  string  $userName  ユーザー名
     * @param  Carbon  $date  対象月
     * @return string CSVファイル名
     */
    public function generateFileName(
        string $userName,
        Carbon $date
    ): string {
        return sprintf(
            '%s_%s.csv',
            $userName,
            $date->format('Y-m')
        );
    }
}
