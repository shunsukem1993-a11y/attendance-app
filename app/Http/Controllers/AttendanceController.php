<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceStoreRequest;
use App\Services\AttendanceDetailService;
use App\Services\AttendanceListService;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private AttendanceListService $attendanceListService,
        private AttendanceDetailService $attendanceDetailService
    ) {}

    /**
     * 勤怠登録画面を表示する。
     *
     * @return View 勤怠登録画面
     */
    public function create(): View
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
     *
     * @param  AttendanceStoreRequest  $request  勤怠打刻リクエスト
     * @return RedirectResponse 勤怠登録画面へのリダイレクト
     */
    public function store(AttendanceStoreRequest $request): RedirectResponse
    {
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

    /**
     * 勤怠一覧画面を表示する。
     *
     * @param  Request  $request  月指定を含むリクエスト
     * @return View 勤怠一覧画面
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $date = $request->filled('date')
            ? now()->createFromFormat('Y-m', $request->input('date'))
            : now()->startOfMonth();

        $formattedAttendanceRecords = $this->attendanceListService->getMonthlyRecords(
            $user,
            $date
        );

        $previousMonth = $date->copy()->subMonth()->format('Y-m');
        $nextMonth = $date->copy()->addMonth()->format('Y-m');

        return view('user.user-attendance-list', compact(
            'date',
            'previousMonth',
            'nextMonth',
            'formattedAttendanceRecords'
        ));
    }

    /**
     * 勤怠詳細画面を表示する。
     *
     * @param  int  $id  勤怠記録ID
     * @return View 勤怠詳細画面
     */
    public function show(int $id): View
    {
        $loginUser = Auth::user();
        $user = $loginUser;

        $attendanceRecord = $this->attendanceDetailService
            ->getAttendanceDetail($user, $id);

        $data = $this->attendanceDetailService
            ->formatAttendanceDetail($attendanceRecord);

        return view('user.user-detail', compact(
            'user',
            'loginUser',
            'data'
        ));
    }
}
