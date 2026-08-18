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
