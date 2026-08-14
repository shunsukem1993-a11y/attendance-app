<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

class AdminLogoutController extends Controller
{
    public function __construct(
        private AuthenticatedSessionController $logoutController
    ) {}

    public function destroy(Request $request)
    {
        // 管理者ログアウトであることをRequestに保存
        $request->attributes->set('is_admin_logout', true);

        // Fortifyのログアウト処理を実行
        return $this->logoutController->destroy($request);
    }
}
