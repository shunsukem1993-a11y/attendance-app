<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest as AttendanceCorrectionRequestForm;
use App\Services\AttendanceCorrectionRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AttendanceCorrectionRequestController extends Controller
{
    public function __construct(
        private AttendanceCorrectionRequestService $attendanceCorrectionRequestService
    ) {}

    /**
     * 勤怠修正申請一覧を表示する。
     */
    public function index()
    {
        $user = Auth::user();

        $formattedApplications = $this->attendanceCorrectionRequestService
            ->getApplications($user);

        return view('user.user-application-list', compact(
            'user',
            'formattedApplications'
        ));
    }

    /**
     * 勤怠修正申請の詳細から勤怠詳細画面へ遷移する。
     */
    public function show(int $id)
    {
        $user = Auth::user();

        $application = $this->attendanceCorrectionRequestService
            ->getApplication($user, $id);

        return redirect()->route('attendance.detail', [
            'id' => $application->attendance_record_id,
        ]);
    }

    /**
     * 勤怠修正申請を登録する。
     */
    public function store(
        AttendanceCorrectionRequestForm $request,
        int $id
    ): RedirectResponse {
        $user = Auth::user();

        $this->attendanceCorrectionRequestService->create(
            $user,
            $id,
            $request->validated()
        );

        return redirect()->route('attendance.detail', [
            'id' => $id,
        ]);
    }
}
