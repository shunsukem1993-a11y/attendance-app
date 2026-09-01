<?php

namespace App\Http\Controllers;

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
}
