<?php

namespace Tests\Support;

use App\Models\AttendanceRecord;
use App\Models\User;

trait AttendanceReportTestHelper
{
    /**
     * 勤怠レポートのテストデータを作成する。
     */
    protected function createReportTestRecords(User $user): void
    {
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->subMonth()->startOfMonth()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->subMonths(2)->startOfMonth()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->startOfMonth()->addDay()->toDateString(),
            'clock_in' => '09:30:00',
            'clock_out' => '18:00:00',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->startOfMonth()->addDays(2)->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->startOfMonth()->addDays(3)->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '20:00:00',
        ]);
    }
}
