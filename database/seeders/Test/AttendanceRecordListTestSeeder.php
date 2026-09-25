<?php

namespace Database\Seeders\Test;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceRecordListTestSeeder extends Seeder
{
    /**
     * 勤怠一覧APIテスト用のデータを作成する。
     */
    public function run(): void
    {
        $user = User::where('email', 'user1@example.com')
            ->firstOrFail();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-09-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '一覧APIテスト用勤務1',
        ]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-09-02',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '一覧APIテスト用勤務2',
        ]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-09-03',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '一覧APIテスト用勤務3',
        ]);
    }
}
