<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectionRequest;
use App\Services\AdminAttendanceCorrectionRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminAttendanceCorrectionRequestController extends Controller
{
    public function __construct(
        private AdminAttendanceCorrectionRequestService $adminAttendanceCorrectionRequestService
    ) {}

    /**
     * 管理者の勤怠修正申請一覧画面を表示する。
     *
     * @return View 管理者の勤怠修正申請一覧画面
     */
    public function index(): View
    {
        $applications = $this->adminAttendanceCorrectionRequestService
            ->getApplications();

        return view('admin.admin-application-list', compact(
            'applications'
        ));
    }

    /**
     * 管理者の勤怠修正申請詳細画面を表示する。
     *
     * @param  int  $id  勤怠修正申請ID
     * @return View 管理者の勤怠修正申請詳細画面
     */
    public function show(int $id): View
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
     *
     * @param  int  $id  勤怠修正申請ID
     * @return RedirectResponse 勤怠修正申請詳細画面へのリダイレクト
     */
    public function approve(int $id): RedirectResponse
    {
        $this->adminAttendanceCorrectionRequestService
            ->approve($id);

        return redirect()
            ->route('admin.attendance.correction.show', $id);
    }
}
