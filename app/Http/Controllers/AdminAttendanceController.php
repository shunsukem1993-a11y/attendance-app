<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest as AttendanceCorrectionRequestForm;
use App\Models\User;
use App\Services\AdminAttendanceDetailService;
use App\Services\AdminAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAttendanceController extends Controller
{
    public function __construct(
        private AdminAttendanceService $adminAttendanceService,
        private AdminAttendanceDetailService $adminAttendanceDetailService
    ) {}

    /**
     * 管理者勤怠一覧画面を表示する。
     */
    public function index(Request $request)
    {
        $date = $request->filled('date')
            ? Carbon::createFromFormat(
                'Y-m-d',
                $request->input('date')
            )
            : Carbon::today();

        $users = $this->adminAttendanceService->getUsers();

        $attendanceRecords = $this->adminAttendanceService
            ->getDailyRecords($date);

        $previousDay = $date->copy()
            ->subDay()
            ->format('Y-m-d');

        $nextDay = $date->copy()
            ->addDay()
            ->format('Y-m-d');

        return view('admin.admin-attendance-list', compact(
            'date',
            'previousDay',
            'nextDay',
            'users',
            'attendanceRecords'
        ));
    }

    /**
     * 管理者の勤怠詳細画面を表示する。
     */
    public function show(int $id)
    {
        $loginUser = Auth::user();

        $attendanceRecord = $this->adminAttendanceDetailService
            ->getAttendanceDetail($id);

        $data = $this->adminAttendanceDetailService
            ->formatAttendanceDetail($attendanceRecord);

        return view('admin.admin-detail', [
            'user' => $attendanceRecord->user,
            'loginUser' => $loginUser,
            'attendanceRecord' => $data,
        ]);
    }

    /**
     * 管理者が勤怠を直接修正する。
     */
    public function update(
        AttendanceCorrectionRequestForm $request,
        int $id
    ) {
        $this->adminAttendanceDetailService->updateAttendance(
            $id,
            $request->validated()
        );

        return redirect()
            ->route('admin.attendance.detail', $id)
            ->with('success', '勤怠を修正しました。');
    }

    /**
     * スタッフ別月次勤怠一覧画面を表示する。
     */
    public function staff(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $date = $request->filled('date')
            ? Carbon::createFromFormat('Y-m', $request->input('date'))
            : now()->startOfMonth();

        $formattedAttendanceRecords =
            $this->adminAttendanceService->getStaffMonthlyRecords(
                $user,
                $date
            );

        $previousMonth = $date->copy()
            ->subMonth()
            ->format('Y-m');

        $nextMonth = $date->copy()
            ->addMonth()
            ->format('Y-m');

        return view('admin.staff-attendance-list', compact(
            'user',
            'date',
            'previousMonth',
            'nextMonth',
            'formattedAttendanceRecords'
        ));
    }
}
