<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * 一般ユーザーをログインさせる
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $credentials['admin_status'] = false;

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'ログイン情報が登録されていません',
                ])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        return redirect()->route('attendance.create');
    }
}
