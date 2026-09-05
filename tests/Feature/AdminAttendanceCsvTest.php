<?php

namespace Tests\Feature;

use App\Models\AttendanceBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AdminAttendanceCsvTestHelper;
use Tests\TestCase;

class AdminAttendanceCsvTest extends TestCase
{
    use AdminAttendanceCsvTestHelper;
    use RefreshDatabase;

    /**
     * 管理者がスタッフの月次勤怠をCSV出力できる。
     */
    public function test_admin_can_export_staff_monthly_attendance_as_csv(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendanceRecord = $this->createAttendanceRecord(
            $user,
            '2026-09-01'
        );

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $response = $this->exportCsv($admin, $user);

        $response->assertOk();

        $response->assertHeader(
            'Content-Type',
            'text/csv; charset=UTF-8'
        );

        $contentDisposition = $response->headers->get(
            'Content-Disposition'
        );

        $this->assertStringContainsString(
            rawurlencode($user->name.'_2026-09.csv'),
            $contentDisposition
        );
    }

    /**
     * CSVに対象ユーザーの勤怠情報が正しく出力される。
     */
    public function test_csv_contains_staff_attendance_data(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendanceRecord = $this->createAttendanceRecord(
            $user,
            '2026-09-01'
        );

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $response = $this->exportCsv($admin, $user);

        $csv = $response->streamedContent();

        $this->assertStringContainsString(
            '日付,出勤,退勤,休憩,合計',
            $csv
        );

        $this->assertStringContainsString(
            '09/01,09:00,18:00,1:00,8:00',
            $csv
        );
    }

    /**
     * 対象ユーザー・対象年月以外の勤怠情報がCSVに出力されない。
     */
    public function test_csv_does_not_contain_other_users_or_months_attendance(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $otherUser = $this->createUser();

        // 対象ユーザー・対象年月 → 出力される
        $this->createAttendanceRecord(
            $user,
            '2026-09-01'
        );

        // 対象ユーザー・別年月 → 出力されない
        $this->createAttendanceRecord(
            $user,
            '2026-08-01',
            '10:00:00',
            '19:00:00'
        );

        // 別ユーザー・対象年月 → 出力されない
        $this->createAttendanceRecord(
            $otherUser,
            '2026-09-01',
            '11:00:00',
            '20:00:00'
        );

        $response = $this->exportCsv($admin, $user);

        $csv = $response->streamedContent();

        // 対象ユーザー・対象年月の勤怠は出力される
        $this->assertStringContainsString(
            '09/01,09:00,18:00',
            $csv
        );

        // 対象ユーザーの別年月の勤怠は出力されない
        $this->assertStringNotContainsString(
            '08/01,10:00,19:00',
            $csv
        );

        // 別ユーザーの勤怠は出力されない
        $this->assertStringNotContainsString(
            '09/01,11:00,20:00',
            $csv
        );
    }

    /**
     * 勤怠情報が存在しない項目はCSVで空欄になる。
     */
    public function test_csv_outputs_blank_when_attendance_information_is_missing(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        // 出勤・退勤がある勤怠
        $this->createAttendanceRecord(
            $user,
            '2026-09-01'
        );

        // 出勤のみで退勤がない勤怠
        $this->createAttendanceRecord(
            $user,
            '2026-09-02',
            '09:00:00',
            null
        );

        // 勤怠レコードが存在しない2026-09-03は、
        // 月次勤怠一覧に空欄の行として出力される
        $response = $this->exportCsv($admin, $user);

        $csv = $response->streamedContent();

        // 出勤・退勤がある日は正常に出力される
        $this->assertStringContainsString(
            '09/01,09:00,18:00,,9:00',
            $csv
        );

        // 退勤がない場合、退勤・休憩・合計が空欄になる
        $this->assertStringContainsString(
            '09/02,09:00,,,',
            $csv
        );

        // 勤怠レコードがない日は、すべて空欄になる
        $this->assertStringContainsString(
            '09/03,,,,',
            $csv
        );
    }
}
