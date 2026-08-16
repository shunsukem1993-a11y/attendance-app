<?php

use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminLogoutController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 会員登録画面
Route::get('/register', [RegisterController::class, 'create'])
    ->name('register');

// 会員登録処理
Route::post('/register', [RegisterController::class, 'store'])
    ->name('register.store');

// 一般ユーザーログイン画面
Route::get('/login', [LoginController::class, 'create'])
    ->name('login');

// 一般ユーザーログイン処理
Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store');

// 管理者ログイン画面
Route::get('/admin/login', [AdminLoginController::class, 'create'])
    ->name('admin.login');

// 管理者ログイン処理
Route::post('/admin/login', [AdminLoginController::class, 'store'])
    ->name('admin.login.store');

// 管理者ログアウト処理
Route::post('/admin/logout', [AdminLogoutController::class, 'destroy'])
    ->middleware('auth')
    ->name('admin.logout');

// 一般ユーザー
Route::middleware('auth')->group(function () {

    // 勤怠登録画面
    Route::get('/attendance', [AttendanceController::class, 'create'])
        ->name('attendance.create');

    // 勤怠打刻処理
    Route::post('/attendance', [AttendanceController::class, 'store'])
        ->name('attendance.store');
});

// 管理者
Route::middleware(['auth', 'admin'])->group(function () {

    // 管理者ログアウト処理
    Route::post('/admin/logout', [AdminLogoutController::class, 'destroy'])
        ->name('admin.logout');

    // 管理者勤怠一覧画面
    Route::get('/admin/attendance/list', function () {
        $date = Carbon::today();

        $previousDay = $date->copy()->subDay()->format('Y-m-d');
        $nextDay = $date->copy()->addDay()->format('Y-m-d');

        $users = collect();
        $attendanceRecords = collect();

        return view('admin.admin-attendance-list', compact(
            'date',
            'previousDay',
            'nextDay',
            'users',
            'attendanceRecords'
        ));
    })->name('admin.attendance.list');
});
