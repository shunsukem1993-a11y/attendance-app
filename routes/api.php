<?php

use App\Http\Controllers\Api\V1\AttendanceRecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    // 勤怠一覧取得API
    Route::get(
        '/attendance-records',
        [AttendanceRecordController::class, 'index']
    );

    // 勤怠詳細取得API
    Route::get(
        '/attendance-records/{attendanceRecord}',
        [AttendanceRecordController::class, 'show']
    );

    // 勤怠登録API(Sanctum認証済みユーザーのみ)
    Route::middleware('auth:sanctum')->post(
        '/attendance-records',
        [AttendanceRecordController::class, 'store']
    );
});
