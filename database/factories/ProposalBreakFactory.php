<?php

namespace Database\Factories;

use App\Models\AttendanceCorrectionRequest;
use App\Models\ProposalBreak;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProposalBreak>
 */
class ProposalBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendance_correction_request_id' => AttendanceCorrectionRequest::factory(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ];
    }
}
