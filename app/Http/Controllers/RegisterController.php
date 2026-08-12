<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * 会員登録画面を表示する
     */
    public function create(): View
    {
        return view('user.register');
    }

    /**
     * 一般ユーザーを登録する
     */
    public function store(
        RegisterRequest $request,
        CreateNewUser $creator
    ): RedirectResponse {
        $user = $creator->create($request->validated());

        Auth::login($user);

        return redirect()->route('attendance.index');
    }
}
