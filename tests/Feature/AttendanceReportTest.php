<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AttendanceReportTestHelper;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use AttendanceReportTestHelper;
    use RefreshDatabase;

    /**
     * 未認証ユーザーは勤怠レポート画面にアクセスできない。
     */
    public function test_guest_cannot_access_attendance_report(): void
    {
        $response = $this->get('/attendance/report');

        $response->assertRedirect('/login');
    }

    /**
     * 認証ユーザーの勤怠統計情報が正しく計算される。
     */
    public function test_authenticated_user_can_see_correct_attendance_statistics(): void
    {
        $user = User::factory()->create();

        $this->createReportTestRecords($user);

        $response = $this
            ->actingAs($user)
            ->get('/attendance/report');

        $response->assertOk();

        $response->assertViewHas('summary', [
            'total_work_minutes' => 1020,
            'total_overtime_minutes' => 60,
            'avg_work_minutes' => 510,
        ]);

        $response->assertViewHas('monthlyTrend', function ($monthlyTrend) {
            $months = $monthlyTrend->keyBy('month');

            $previousMonth = now()->subMonth()->format('Y-m');
            $twoMonthsAgo = now()->subMonths(2)->format('Y-m');

            return $monthlyTrend->count() === 6
                && $months[$previousMonth]['work_minutes'] === 540
                && $months[$previousMonth]['overtime_minutes'] === 60
                && $months[$twoMonthsAgo]['work_minutes'] === 480
                && $months[$twoMonthsAgo]['overtime_minutes'] === 0;
        });

        $response->assertViewHas('anomalies', [
            'late_count' => 1,
            'early_leave_count' => 1,
            'long_work_count' => 1,
        ]);
    }

    /**
     * 勤怠記録がないユーザーでもエラーにならず、
     * 0または空の統計情報が表示される。
     */
    public function test_user_without_attendance_records_can_see_empty_report(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/attendance/report');

        $response->assertOk();

        $response->assertViewHas('summary', [
            'total_work_minutes' => 0,
            'total_overtime_minutes' => 0,
            'avg_work_minutes' => 0,
        ]);

        $response->assertViewHas('monthlyTrend', function ($monthlyTrend) {
            return $monthlyTrend->count() === 6
                && $monthlyTrend->every(function (array $month): bool {
                    return $month['work_minutes'] === 0
                        && $month['overtime_minutes'] === 0;
                });
        });

        $response->assertViewHas('anomalies', [
            'late_count' => 0,
            'early_leave_count' => 0,
            'long_work_count' => 0,
        ]);
    }
}
