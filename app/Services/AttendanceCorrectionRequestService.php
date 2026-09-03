<?php

namespace App\Services;

use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Support\Collection;

class AttendanceCorrectionRequestService
{
    /**
     * 一般ユーザー自身の勤怠修正申請を取得する。
     *
     * @param  User  $user  対象ユーザー
     * @return Collection<int, array<string, mixed>> 勤怠修正申請のCollection
     */
    public function getApplications(User $user): Collection
    {
        return AttendanceCorrectionRequest::with([
            'attendanceRecord',
        ])
            ->where('user_id', $user->id)
            ->get()
            ->map(function (
                AttendanceCorrectionRequest $application
            ) {
                return [
                    'id' => $application->id,
                    'attendance_record_id' => $application->attendance_record_id,
                    'approval_status' => $application->approval_status ===
                        AttendanceCorrectionRequest::STATUS_PENDING
                            ? '承認待ち'
                            : '承認済み',
                    'date' => $application->attendanceRecord->date,
                    'comment' => $application->comment,
                    'application_date' => $application->created_at->format(
                        'Y-m-d H:i:s'
                    ),
                ];
            });
    }

    /**
     * 一般ユーザー自身の勤怠修正申請を取得する。
     *
     * @param  User  $user  対象ユーザー
     * @param  int  $id  勤怠修正申請ID
     * @return AttendanceCorrectionRequest 勤怠修正申請
     */
    public function getApplication(
        User $user,
        int $id
    ): AttendanceCorrectionRequest {
        return AttendanceCorrectionRequest::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
    }

    /**
     * 一般ユーザーの勤怠修正申請を登録する。
     *
     * @param  User  $user  対象ユーザー
     * @param  int  $attendanceRecordId  勤怠記録ID
     * @param  array<string, mixed>  $data  申請データ
     * @return AttendanceCorrectionRequest 作成した勤怠修正申請
     */
    public function create(
        User $user,
        int $attendanceRecordId,
        array $data
    ): AttendanceCorrectionRequest {
        $attendanceRecord = AttendanceRecord::where('id', $attendanceRecordId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return AttendanceCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_record_id' => $attendanceRecord->id,
            'approval_status' => AttendanceCorrectionRequest::STATUS_PENDING,
            'comment' => $data['comment'],
            'new_date' => $attendanceRecord->date,
            'new_clock_in' => $data['new_clock_in'],
            'new_clock_out' => $data['new_clock_out'],
        ]);
    }
}
