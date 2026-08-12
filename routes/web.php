<?php

use App\Http\Controllers\RegisterController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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

Route::middleware('auth')->group(function () {

    // 仮の勤怠登録画面
    Route::get('/attendance', function () {
        $user = Auth::user();

        $formattedDate = Carbon::now()->format('Y年m月d日');
        $formattedTime = Carbon::now()->format('H:i');

        // 仮の勤怠状態
        $user->attendance_status = '勤務外';

        return view('user.attendance-register', compact(
            'user',
            'formattedDate',
            'formattedTime'
        ));
    })->name('attendance.index');
});
