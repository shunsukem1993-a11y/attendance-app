<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceCorrectionRequestListController extends Controller
{
    /**
     * 勤怠修正申請一覧画面を表示する。
     */
    public function index(Request $request)
    {
        if ($request->attributes->get('is_admin')) {
            return app(AdminAttendanceCorrectionRequestController::class)
                ->index();
        }

        return app(AttendanceCorrectionRequestController::class)
            ->index();
    }
}
