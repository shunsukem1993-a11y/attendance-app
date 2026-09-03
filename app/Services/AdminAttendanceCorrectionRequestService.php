<?php

namespace App\Services;

use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminAttendanceCorrectionRequestService
{
    /**
     * 全一般ユーザーの勤怠修正申請を取得する。
     */
    public function getApplications(): Collection
    {
        return AttendanceCorrectionRequest::with([
            'user',
            'attendanceRecord',
        ])
            ->whereHas('user', function ($query) {
                $query->where('admin_status', false);
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(function (AttendanceCorrectionRequest $application) {
                return $this->formatApplication($application);
            });
    }

    /**
     * 管理者の申請一覧画面で使用する形式に整形する。
     */
    private function formatApplication(
        AttendanceCorrectionRequest $application
    ): AttendanceCorrectionRequest {
        $application->approval_status =
            $application->approval_status === AttendanceCorrectionRequest::STATUS_PENDING
                ? '承認待ち'
                : '承認済み';

        $application->application_date =
            $application->created_at->format('Y/m/d');

        return $application;
    }

    /**
     * 指定された勤怠修正申請の詳細を取得する。
     */
    public function getApplication(int $id): AttendanceCorrectionRequest
    {
        $application = AttendanceCorrectionRequest::with([
            'user',
            'attendanceRecord',
            'proposalBreaks',
        ])->findOrFail($id);

        if ($application->new_clock_in) {
            $application->new_clock_in = Carbon::parse(
                $application->new_clock_in
            )->format('H:i');
        }

        if ($application->new_clock_out) {
            $application->new_clock_out = Carbon::parse(
                $application->new_clock_out
            )->format('H:i');
        }

        return $application;
    }

    /**
     * 勤怠修正申請を承認する。
     */
    public function approve(int $id): void
    {
        $application = AttendanceCorrectionRequest::with([
            'attendanceRecord',
            'proposalBreaks',
        ])->findOrFail($id);

        $attendanceRecord = $application->attendanceRecord;

        // 勤怠情報を申請内容で更新
        $attendanceRecord->update([
            'date' => $application->new_date,
            'clock_in' => $application->new_clock_in,
            'clock_out' => $application->new_clock_out,
            'comment' => $application->comment,
        ]);

        // 既存の休憩情報を削除
        $attendanceRecord->breaks()->delete();

        // 申請された休憩情報を勤怠に反映
        foreach ($application->proposalBreaks as $proposalBreak) {
            AttendanceBreak::create([
                'attendance_record_id' => $attendanceRecord->id,
                'break_in' => $proposalBreak->break_in,
                'break_out' => $proposalBreak->break_out,
            ]);
        }

        // 申請を承認済みに変更
        $application->update([
            'approval_status' => AttendanceCorrectionRequest::STATUS_APPROVED,
        ]);
    }
}
