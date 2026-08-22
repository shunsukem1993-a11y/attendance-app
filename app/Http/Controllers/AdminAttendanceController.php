<?php

namespace App\Http\Controllers;

use App\Services\AdminAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
}
