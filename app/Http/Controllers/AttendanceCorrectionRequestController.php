<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest as AttendanceCorrectionRequestForm;
use App\Services\AttendanceCorrectionRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceCorrectionRequestController extends Controller
{
    public function __construct(
        private AttendanceCorrectionRequestService $attendanceCorrectionRequestService
    ) {}

    /**
     * 勤怠修正申請一覧を表示する。
     *
     * @return View 勤怠修正申請一覧画面
     */
    public function index(): View
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
     *
     * @param  int  $id  勤怠修正申請ID
     * @return RedirectResponse 勤怠詳細画面へのリダイレクト
     */
    public function show(int $id): RedirectResponse
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
     *
     * @param  AttendanceCorrectionRequestForm  $request  勤怠修正リクエスト
     * @param  int  $id  勤怠記録ID
     * @return RedirectResponse 勤怠詳細画面へのリダイレクト
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
