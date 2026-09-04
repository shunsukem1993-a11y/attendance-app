<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /**
     * 一般ユーザーを登録する。
     *
     * @param  RegisterRequest  $request  ユーザー登録リクエスト
     * @param  CreateNewUser  $creator  ユーザー作成アクション
     * @return RedirectResponse 登録後のリダイレクト
     */
    public function store(
        RegisterRequest $request,
        CreateNewUser $creator
    ): RedirectResponse {
        $user = $creator->create($request->validated());

        Auth::login($user);

        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice');
    }
}
