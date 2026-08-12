<?php

namespace Database\Factories;

use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceCorrectionRequest>
 */
class AttendanceCorrectionRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'attendance_record_id' => AttendanceRecord::factory(),
            'approval_status' => 0,
            'comment' => fake()->sentence(),
            'new_date' => fake()->date(),
            'new_clock_in' => '09:30:00',
            'new_clock_out' => '18:00:00',
        ];
    }
}
