<?php

namespace App\Services;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;

class AttendanceService
{
    /**
     * 今日の勤怠記録を取得する。
     */
    public function getTodayRecord(User $user): ?AttendanceRecord
    {
        return AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();
    }

    /**
     * 勤怠ステータスを取得する。
     */
    public function getStatus(?AttendanceRecord $attendanceRecord): string
    {
        if (! $attendanceRecord) {
            return '勤務外';
        }

        if ($attendanceRecord->clock_out !== null) {
            return '退勤済';
        }

        $activeBreak = $attendanceRecord->breaks
            ->contains(fn (AttendanceBreak $break) => $break->break_out === null);

        return $activeBreak ? '休憩中' : '出勤中';
    }

    /**
     * 勤怠打刻を処理する。
     */
    public function process(User $user, string $action): ?string
    {
        return match ($action) {
            'clock_in' => $this->clockIn($user),
            'clock_out' => $this->clockOut($user),
            'break_in' => $this->breakIn($user),
            'break_out' => $this->breakOut($user),
            default => null,
        };
    }

    /**
     * 出勤する。
     */
    private function clockIn(User $user): ?string
    {
        $attendanceRecord = $this->getTodayRecord($user);

        if ($attendanceRecord) {
            return '本日はすでに出勤済みです。';
        }

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->format('H:i:s'),
        ]);

        return null;
    }

    /**
     * 休憩を開始する。
     */
    private function breakIn(User $user): ?string
    {
        $attendanceRecord = $this->getTodayRecord($user);

        if (! $attendanceRecord) {
            return '出勤していないため、休憩を開始できません。';
        }

        if ($attendanceRecord->clock_out !== null) {
            return '退勤済みのため、休憩を開始できません。';
        }

        if ($this->hasActiveBreak($attendanceRecord)) {
            return '現在休憩中です。';
        }

        AttendanceBreak::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => now()->format('H:i:s'),
        ]);

        return null;
    }

    /**
     * 休憩を終了する。
     */
    private function breakOut(User $user): ?string
    {
        $attendanceRecord = $this->getTodayRecord($user);

        if (! $attendanceRecord) {
            return '本日の勤怠記録がありません。';
        }

        $break = AttendanceBreak::where(
            'attendance_record_id',
            $attendanceRecord->id
        )
            ->whereNull('break_out')
            ->latest('id')
            ->first();

        if (! $break) {
            return '現在休憩中ではありません。';
        }

        $break->update([
            'break_out' => now()->format('H:i:s'),
        ]);

        return null;
    }

    /**
     * 退勤する。
     */
    private function clockOut(User $user): ?string
    {
        $attendanceRecord = $this->getTodayRecord($user);

        if (! $attendanceRecord) {
            return '本日の勤怠記録がありません。';
        }

        if ($attendanceRecord->clock_out !== null) {
            return 'すでに退勤済みです。';
        }

        if ($this->hasActiveBreak($attendanceRecord)) {
            return '休憩中は退勤できません。';
        }

        $attendanceRecord->update([
            'clock_out' => now()->format('H:i:s'),
        ]);

        return null;
    }

    /**
     * 現在休憩中か確認する。
     */
    private function hasActiveBreak(AttendanceRecord $attendanceRecord): bool
    {
        return $attendanceRecord->breaks
            ->contains(fn (AttendanceBreak $break) => $break->break_out === null);
    }
}
