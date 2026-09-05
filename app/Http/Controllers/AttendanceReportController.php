<?php

namespace App\Http\Controllers;

use App\Services\AttendanceReportAnomalyService;
use App\Services\AttendanceReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    public function __construct(
        private AttendanceReportService $attendanceReportService,
        private AttendanceReportAnomalyService $anomalyService
    ) {}

    /**
     * マイ勤怠レポート画面を表示する。
     *
     * ログインユーザーの過去6か月分と今月の勤怠情報を取得し、
     * 基本サマリー、月次推移、異常勤怠を画面に渡す。
     *
     * @return View マイ勤怠レポート画面
     */
    public function index(): View
    {
        $user = Auth::user();

        $attendanceRecords = $this->attendanceReportService
            ->getAttendanceRecords($user);

        $dailyResults = $this->attendanceReportService
            ->calculateDailyResults($attendanceRecords);

        $summary = $this->attendanceReportService
            ->getSummary($dailyResults);

        $monthlyTrend = $this->attendanceReportService
            ->getMonthlySummary($dailyResults);

        $anomalies = $this->anomalyService
            ->getAnomalies($dailyResults);

        return view('reports.index', compact(
            'summary',
            'monthlyTrend',
            'anomalies'
        ));
    }
}
