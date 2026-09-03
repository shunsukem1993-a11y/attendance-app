<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class AdminStaffController extends Controller
{
    /**
     * スタッフ一覧画面を表示する。
     *
     * @return View スタッフ一覧画面
     */
    public function index(): View
    {
        $users = User::where('admin_status', false)
            ->get();

        return view('admin.staff-list', compact('users'));
    }
}
