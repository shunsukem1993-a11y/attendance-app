<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceCorrectionRequestListController extends Controller
{
    /**
     * 勤怠修正申請一覧画面を表示する。
     *
     * @param  Request  $request  管理者判定情報を含むリクエスト
     * @return View 勤怠修正申請一覧画面
     */
    public function index(Request $request): View
    {
        if ($request->attributes->get('is_admin')) {
            return app(AdminAttendanceCorrectionRequestController::class)
                ->index();
        }

        return app(AttendanceCorrectionRequestController::class)
            ->index();
    }
}
