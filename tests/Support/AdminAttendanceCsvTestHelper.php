<?php

namespace Tests\Support;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Testing\TestResponse;

trait AdminAttendanceCsvTestHelper
{
    /**
     * 管理者ユーザーを作成する。
     *
     * @return User 管理者ユーザー
     */
    protected function createAdmin(): User
    {
        return User::factory()->create([
            'admin_status' => true,
        ]);
    }

    /**
     * 一般ユーザーを作成する。
     *
     * @return User 一般ユーザー
     */
    protected function createUser(): User
    {
        return User::factory()->create([
            'admin_status' => false,
        ]);
    }

    /**
     * CSV出力リクエストを実行する。
     *
     * @param  User  $admin  管理者ユーザー
     * @param  User  $user  対象スタッフ
     * @param  string  $yearMonth  対象年月
     * @return TestResponse CSVレスポンス
     */
    protected function exportCsv(
        User $admin,
        User $user,
        string $yearMonth = '2026-09'
    ): TestResponse {
        return $this
            ->actingAs($admin)
            ->post(route('admin.attendance.export'), [
                'user_id' => $user->id,
                'year_month' => $yearMonth,
            ]);
    }

    /**
     * 勤怠記録を作成する。
     *
     * @param  User  $user  対象ユーザー
     * @param  string  $date  勤怠日
     * @param  string|null  $clockIn  出勤時刻
     * @param  string|null  $clockOut  退勤時刻
     * @return AttendanceRecord 勤怠記録
     */
    protected function createAttendanceRecord(
        User $user,
        string $date,
        ?string $clockIn = '09:00:00',
        ?string $clockOut = '18:00:00'
    ): AttendanceRecord {
        return AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);
    }
}
