<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Services\AttendanceListService;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private AttendanceListService $attendanceListService
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

    /**
     * 勤怠一覧画面を表示する。
     */
    public function index(Request $request)
    {
        $loginUser = Auth::user();
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
     */
    public function show(int $id)
    {
        $loginUser = Auth::user();
        $user = $loginUser;

        $attendanceRecord = AttendanceRecord::with([
            'breaks',
            'correctionRequests',
        ])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $data = [
            'id' => $attendanceRecord->id,
            'application' => $attendanceRecord->correctionRequests
                ->where(
                    'approval_status',
                    AttendanceCorrectionRequest::STATUS_PENDING
                )
                ->first(),
            'year' => Carbon::parse($attendanceRecord->date)->format('Y'),
            'date' => Carbon::parse($attendanceRecord->date)->format('m/d'),
            'clock_in' => $attendanceRecord->clock_in
                ? Carbon::parse($attendanceRecord->clock_in)->format('H:i')
                : '',
            'clock_out' => $attendanceRecord->clock_out
                ? Carbon::parse($attendanceRecord->clock_out)->format('H:i')
                : '',
            'breaks' => $attendanceRecord->breaks->map(function ($break) {
                return [
                    'break_in' => $break->break_in
                        ? Carbon::parse($break->break_in)->format('H:i')
                        : '',
                    'break_out' => $break->break_out
                        ? Carbon::parse($break->break_out)->format('H:i')
                        : '',
                ];
            })->values()->all(),
            'comment' => $attendanceRecord->comment ?? '',
        ];

        return view('user.user-detail', compact(
            'user',
            'loginUser',
            'data'
        ));
    }
}
