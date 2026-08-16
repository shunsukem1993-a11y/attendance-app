<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService
    ) {}

    /**
     * 勤怠登録画面を表示する。
     */
    public function create()
    {
        $user = Auth::user();

        $attendanceRecord = $this->attendanceService->getTodayRecord($user);

        $user->attendance_status = $this->attendanceService->getStatus(
            $attendanceRecord
        );

        $formattedDate = now()->format('Y年m月d日');
        $formattedTime = now()->format('H:i');

        return view('user.attendance-register', compact(
            'user',
            'formattedDate',
            'formattedTime',
            'attendanceRecord'
        ));
    }

    /**
     * 勤怠打刻を処理する。
     */
    public function store(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:clock_in,clock_out,break_in,break_out'],
        ]);

        $error = $this->attendanceService->process(
            Auth::user(),
            $request->input('action')
        );

        if ($error) {
            return redirect()
                ->route('attendance.create')
                ->with('error', $error);
        }

        return redirect()->route('attendance.create');
    }
}
