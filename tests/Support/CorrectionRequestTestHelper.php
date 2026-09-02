<?php

namespace Tests\Support;

use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;

trait CorrectionRequestTestHelper
{
    protected function createCorrectionRequest(
        User $user,
        AttendanceRecord $attendanceRecord,
        int $approvalStatus,
        array $attributes = []
    ): AttendanceCorrectionRequest {
        return AttendanceCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_record_id' => $attendanceRecord->id,
            'approval_status' => $approvalStatus,
            ...$attributes,
        ]);
    }
}
