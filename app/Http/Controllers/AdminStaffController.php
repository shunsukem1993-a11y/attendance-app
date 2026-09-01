<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminStaffController extends Controller
{
    /**
     * スタッフ一覧画面を表示する。
     */
    public function index()
    {
        $users = User::where('admin_status', false)
            ->get();

        return view('admin.staff-list', compact('users'));
    }
}
