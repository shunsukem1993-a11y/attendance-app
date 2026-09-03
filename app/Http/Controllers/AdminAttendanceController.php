<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest as AttendanceCorrectionRequestForm;
use App\Models\User;
use App\Services\AdminAttendanceDetailService;
use App\Services\AdminAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAttendanceController extends Controller
{
    public function __construct(
        private AdminAttendanceService $adminAttendanceService,
        private AdminAttendanceDetailService $adminAttendanceDetailService
    ) {}

    /**
     * 管理者勤怠一覧画面を表示する。
     *
     * @param  Request  $request  日付指定を含むリクエスト
     * @return View 管理者勤怠一覧画面
     */
    public function index(Request $request): View
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
     *
     * @param  int  $id  勤怠記録ID
     * @return View 管理者勤怠詳細画面
     */
    public function show(int $id): View
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
     *
     * @param  AttendanceCorrectionRequestForm  $request  勤怠修正リクエスト
     * @param  int  $id  勤怠記録ID
     * @return RedirectResponse 勤怠詳細画面へのリダイレクト
     */
    public function update(
        AttendanceCorrectionRequestForm $request,
        int $id
    ): RedirectResponse {
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
     *
     * @param  Request  $request  月指定を含むリクエスト
     * @param  int  $id  スタッフのユーザーID
     * @return View スタッフ別月次勤怠一覧画面
     */
    public function staff(Request $request, int $id): View
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
