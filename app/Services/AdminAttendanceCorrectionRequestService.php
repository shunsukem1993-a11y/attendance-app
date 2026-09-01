<?php

namespace App\Services;

use App\Models\AttendanceCorrectionRequest;
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
}
