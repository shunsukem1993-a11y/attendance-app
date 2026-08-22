<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest as AttendanceCorrectionRequestForm;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AttendanceCorrectionRequestController extends Controller
{
    /**
     * 勤怠修正申請一覧を表示する。
     */
    public function index()
    {
        $user = Auth::user();

        $applications = AttendanceCorrectionRequest::with(['attendanceRecord'])
            ->where('user_id', $user->id)
            ->get();

        $formattedApplications = $applications->map(function (
            AttendanceCorrectionRequest $application
        ) {
            return [
                'id' => $application->id,
                'approval_status' => $application->approval_status === AttendanceCorrectionRequest::STATUS_PENDING
                    ? '承認待ち'
                    : '承認済み',
                'date' => $application->attendanceRecord->date,
                'comment' => $application->comment,
                'application_date' => $application->created_at->format('Y-m-d H:i:s'),
            ];
        });

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

        $application = AttendanceCorrectionRequest::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

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

        $attendanceRecord = AttendanceRecord::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        AttendanceCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_record_id' => $attendanceRecord->id,
            'approval_status' => AttendanceCorrectionRequest::STATUS_PENDING,
            'comment' => $request->input('comment'),
            'new_date' => $attendanceRecord->date,
            'new_clock_in' => $request->input('new_clock_in'),
            'new_clock_out' => $request->input('new_clock_out'),
        ]);

        return redirect()->route('attendance.detail', [
            'id' => $attendanceRecord->id,
        ]);
    }
}
