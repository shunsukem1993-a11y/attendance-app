<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    /**
     * 管理者ログイン画面を表示する。
     *
     * @return View 管理者ログイン画面
     */
    public function create(): View
    {
        return view('admin.admin-login');
    }

    /**
     * 管理者をログインさせる。
     *
     * @param  LoginRequest  $request  ログインリクエスト
     * @return RedirectResponse ログイン後のリダイレクト
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $credentials['admin_status'] = true;

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'ログイン情報が登録されていません',
                ])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        return redirect()->route('admin.attendance.list');
    }
}
