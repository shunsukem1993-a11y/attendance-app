<?php

namespace App\Services;

use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class AdminAttendanceDetailService
{
    /**
     * 指定された勤怠の詳細を取得する。
     *
     * @param  int  $id  勤怠記録ID
     * @return AttendanceRecord 勤怠記録
     */
    public function getAttendanceDetail(int $id): AttendanceRecord
    {
        return AttendanceRecord::with([
            'breaks',
            'correctionRequests',
            'user',
        ])->findOrFail($id);
    }

    /**
     * 勤怠詳細画面で使用する形式に整形する。
     *
     * @param  AttendanceRecord  $attendanceRecord  勤怠記録
     * @return array<string, mixed> 勤怠詳細画面用のデータ
     */
    public function formatAttendanceDetail(
        AttendanceRecord $attendanceRecord
    ): array {
        return [
            'id' => $attendanceRecord->id,

            'application' => $attendanceRecord->correctionRequests
                ->where(
                    'approval_status',
                    AttendanceCorrectionRequest::STATUS_PENDING
                )
                ->first(),

            'year' => Carbon::parse(
                $attendanceRecord->date
            )->format('Y'),

            'date' => Carbon::parse(
                $attendanceRecord->date
            )->format('m/d'),

            'clock_in' => $attendanceRecord->clock_in
                ? Carbon::parse(
                    $attendanceRecord->clock_in
                )->format('H:i')
                : '',

            'clock_out' => $attendanceRecord->clock_out
                ? Carbon::parse(
                    $attendanceRecord->clock_out
                )->format('H:i')
                : '',

            'breaks' => $attendanceRecord->breaks
                ->map(function (AttendanceBreak $break) {
                    return [
                        'break_in' => $break->break_in
                            ? Carbon::parse(
                                $break->break_in
                            )->format('H:i')
                            : '',

                        'break_out' => $break->break_out
                            ? Carbon::parse(
                                $break->break_out
                            )->format('H:i')
                            : '',
                    ];
                })
                ->values()
                ->all(),

            'comment' => $attendanceRecord->comment ?? '',
        ];
    }

    /**
     * 勤怠情報を更新する。
     *
     * @param  int  $id  勤怠記録ID
     * @param  array<string, mixed>  $data  勤怠更新データ
     */
    public function updateAttendance(
        int $id,
        array $data
    ): void {
        $attendanceRecord = AttendanceRecord::with('breaks')
            ->findOrFail($id);

        $attendanceRecord->update([
            'clock_in' => $data['new_clock_in'],
            'clock_out' => $data['new_clock_out'],
            'comment' => $data['comment'],
        ]);

        $breakIns = $data['new_break_in'] ?? [];
        $breakOuts = $data['new_break_out'] ?? [];

        foreach ($attendanceRecord->breaks as $index => $break) {
            $break->update([
                'break_in' => $breakIns[$index] ?? null,
                'break_out' => $breakOuts[$index] ?? null,
            ]);
        }
    }
}
