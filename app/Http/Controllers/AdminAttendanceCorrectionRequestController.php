<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectionRequest;
use App\Services\AdminAttendanceCorrectionRequestService;

class AdminAttendanceCorrectionRequestController extends Controller
{
    public function __construct(
        private AdminAttendanceCorrectionRequestService $adminAttendanceCorrectionRequestService
    ) {}

    /**
     * 管理者の勤怠修正申請一覧画面を表示する。
     */
    public function index()
    {
        $applications = $this->adminAttendanceCorrectionRequestService
            ->getApplications();

        return view('admin.admin-application-list', compact(
            'applications'
        ));
    }

    /**
     * 管理者の勤怠修正申請詳細画面を表示する。
     */
    public function show(int $id)
    {
        $application = $this->adminAttendanceCorrectionRequestService
            ->getApplication($id);

        $application->approval_status =
            $application->approval_status === AttendanceCorrectionRequest::STATUS_PENDING
                ? '承認待ち'
                : '承認済み';

        return view('admin.admin-application-detail', [
            'application' => $application,
            'user' => $application->user,
        ]);
    }

    /**
     * 管理者が勤怠修正申請を承認する。
     */
    public function approve(int $id)
    {
        $this->adminAttendanceCorrectionRequestService
            ->approve($id);

        return redirect()
            ->route('admin.attendance.correction.show', $id);
    }
}
