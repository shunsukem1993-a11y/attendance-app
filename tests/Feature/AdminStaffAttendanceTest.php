<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者が選択したユーザーの勤怠情報を確認できる
     */
    public function test_admin_can_see_selected_user_attendance_records(): void
    {
        $this->createAdminUser();

        $user = User::factory()->create([
            'name' => '一般ユーザー',
            'admin_status' => false,
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->startOfMonth()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->startOfMonth()->addDay()->toDateString(),
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->get(
            '/admin/attendance/staff/'.$user->id
        );

        $response->assertStatus(200);

        $response->assertSee($user->name);

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /**
     * 現在の月が表示される
     */
    public function test_current_month_is_displayed(): void
    {
        $this->createAdminUser();

        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $response = $this->get(
            '/admin/attendance/staff/'.$user->id
        );

        $response->assertStatus(200);

        $response->assertSee(
            now()->format('Y/m')
        );
    }

    /**
     * 前月の勤怠情報が表示される
     */
    public function test_previous_month_is_displayed(): void
    {
        $this->createAdminUser();

        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $previousMonth = now()->subMonth();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => $previousMonth->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->get(
            '/admin/attendance/staff/'.$user->id.
            '?date='.$previousMonth->format('Y-m')
        );

        $response->assertStatus(200);

        $response->assertSee(
            $previousMonth->format('Y/m')
        );

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 翌月の勤怠情報が表示される
     */
    public function test_next_month_is_displayed(): void
    {
        $this->createAdminUser();

        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $nextMonth = now()->addMonth();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth->toDateString(),
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->get(
            '/admin/attendance/staff/'.$user->id.
            '?date='.$nextMonth->format('Y-m')
        );

        $response->assertStatus(200);

        $response->assertSee(
            $nextMonth->format('Y/m')
        );

        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /**
     * 詳細ボタンからその日の勤怠詳細画面へ遷移できる
     */
    public function test_admin_can_navigate_to_attendance_detail(): void
    {
        $this->createAdminUser();

        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->get(
            '/admin/attendance/staff/'.$user->id
        );

        $response->assertStatus(200);

        $response->assertSee(
            '/admin/attendance/detail/'.$attendanceRecord->id
        );
    }

    /**
     * 一般ユーザーはスタッフ別勤怠一覧にアクセスできない
     */
    public function test_general_user_cannot_access_admin_staff_attendance(): void
    {
        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $this->actingAs($user);

        $targetUser = User::factory()->create([
            'admin_status' => false,
        ]);

        $response = $this->get(
            '/admin/attendance/staff/'.$targetUser->id
        );

        $response->assertForbidden();
    }
}
