<?php

namespace Tests;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function createAttendanceUser(): array
    {
        $user = User::factory()->create();

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user);

        return [$user, $attendanceRecord];
    }
}
