<?php

namespace Database\Seeders;

use App\Models\AttendanceCorrectionRequest;
use App\Models\ProposalBreak;
use Illuminate\Database\Seeder;

class ProposalBreakSeeder extends Seeder
{
    public function run(): void
    {
        // 休憩時間を含む修正申請を取得
        $correctionRequests = AttendanceCorrectionRequest::whereIn('comment', [
            '休憩時間を修正したい',
            '出勤時間と休憩時間を修正したい',
            '退勤時間と休憩時間を修正したい',
        ])->get();

        foreach ($correctionRequests as $correctionRequest) {
            ProposalBreak::create([
                'attendance_correction_request_id' => $correctionRequest->id,
                'break_in' => '12:30:00',
                'break_out' => '13:30:00',
            ]);
        }
    }
}
