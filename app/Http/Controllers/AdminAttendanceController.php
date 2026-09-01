<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest as AttendanceCorrectionRequestForm;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use App\Services\AdminAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAttendanceController extends Controller
{
    public function __construct(
        private AdminAttendanceService $adminAttendanceService
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

        $attendanceRecord = AttendanceRecord::with([
            'breaks',
            'correctionRequests',
            'user',
        ])->findOrFail($id);

        // 勤怠対象ユーザー
        $user = $attendanceRecord->user;

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

    /**
     * 管理者が勤怠を直接修正する。
     */
    public function update(
        AttendanceCorrectionRequestForm $request,
        int $id
    ) {
        $attendanceRecord = AttendanceRecord::with('breaks')
            ->findOrFail($id);

        $attendanceRecord->update([
            'clock_in' => $request->validated('new_clock_in'),
            'clock_out' => $request->validated('new_clock_out'),
            'comment' => $request->validated('comment'),
        ]);

        $breakIns = $request->validated('new_break_in', []);
        $breakOuts = $request->validated('new_break_out', []);

        foreach ($attendanceRecord->breaks as $index => $break) {
            $break->update([
                'break_in' => $breakIns[$index] ?? null,
                'break_out' => $breakOuts[$index] ?? null,
            ]);
        }

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
