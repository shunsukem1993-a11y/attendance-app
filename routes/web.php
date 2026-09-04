<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminAttendanceCorrectionRequestController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminLogoutController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionRequestController;
use App\Http\Controllers\AttendanceCorrectionRequestListController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
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

// 会員登録処理
Route::post('/register', [RegisterController::class, 'store'])
    ->name('register.store');

// 一般ユーザーログイン処理
Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store');

// 一般ユーザー
Route::middleware(['auth', 'verified', 'general.user'])->group(function () {

    // 勤怠登録画面
    Route::get('/attendance', [AttendanceController::class, 'create'])
        ->name('attendance.create');

    // 勤怠打刻処理
    Route::post('/attendance', [AttendanceController::class, 'store'])
        ->name('attendance.store');

    // 勤怠一覧画面
    Route::get('/attendance/list', [AttendanceController::class, 'index'])
        ->name('attendance.list');

    // 勤怠詳細画面
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])
        ->name('attendance.detail');

    // 勤怠修正申請処理
    Route::post(
        '/attendance/detail/{id}',
        [AttendanceCorrectionRequestController::class, 'store']
    )->name('attendance.correction.store');

    // 勤怠修正申請詳細画面
    Route::get(
        '/application/detail/{id}',
        [AttendanceCorrectionRequestController::class, 'show']
    )->name('attendance.correction.show');
});

// 勤怠修正申請一覧画面（一般ユーザー用、管理者用）
Route::middleware('auth', 'verified')->group(function () {
    Route::get(
        '/stamp_correction_request/list',
        [AttendanceCorrectionRequestListController::class, 'index']
    )
        ->middleware('correction.request.role')
        ->name('attendance.correction.index');
});

// 管理者ログイン画面
Route::get('/admin/login', [AdminLoginController::class, 'create'])
    ->name('admin.login');

// 管理者ログイン処理
Route::post('/admin/login', [AdminLoginController::class, 'store'])
    ->name('admin.login.store');

// 管理者
Route::middleware(['auth', 'admin'])->group(function () {

    // 管理者ログアウト処理
    Route::post('/admin/logout', [AdminLogoutController::class, 'destroy'])
        ->name('admin.logout');

    // 管理者勤怠一覧画面
    Route::get(
        '/admin/attendance/list',
        [AdminAttendanceController::class, 'index']
    )->name('admin.attendance.list');

    // 管理者勤怠詳細
    Route::get(
        '/admin/attendance/detail/{id}',
        [AdminAttendanceController::class, 'show']
    )->name('admin.attendance.detail');

    // 管理者勤怠修正
    Route::post(
        '/admin/attendance/detail/{id}',
        [AdminAttendanceController::class, 'update']
    )->name('admin.attendance.update');

    // 管理者スタッフ一覧画面
    Route::get(
        '/admin/staff/list',
        [AdminStaffController::class, 'index']
    )->name('admin.staff.list');

    // スタッフ別月次勤怠一覧
    Route::get(
        '/admin/attendance/staff/{id}',
        [AdminAttendanceController::class, 'staff']
    )->name('admin.attendance.staff');

    // 管理者勤怠修正申請詳細画面
    Route::get(
        '/stamp_correction_request/approve/{id}',
        [AdminAttendanceCorrectionRequestController::class, 'show']
    )->name('admin.attendance.correction.show');

    // 管理者勤怠修正申請承認処理
    Route::post(
        '/stamp_correction_request/approve/{id}',
        [AdminAttendanceCorrectionRequestController::class, 'approve']
    )->name('admin.attendance.correction.approve');
});
