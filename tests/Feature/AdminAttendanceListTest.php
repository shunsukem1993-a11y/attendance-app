<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者がその日の全ユーザーの勤怠情報を確認できる
     */
    public function test_admin_can_see_all_users_attendance_records(): void
    {
        $admin = $this->createAdminUser();

        $user1 = User::factory()->create([
            'admin_status' => false,
        ]);

        $user2 = User::factory()->create([
            'admin_status' => false,
        ]);

        // 管理者の勤怠
        AttendanceRecord::factory()->create([
            'user_id' => $admin->id,
            'date' => now()->toDateString(),
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
        ]);

        // 一般ユーザー1の勤怠
        AttendanceRecord::factory()->create([
            'user_id' => $user1->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 一般ユーザー2の勤怠
        AttendanceRecord::factory()->create([
            'user_id' => $user2->id,
            'date' => now()->toDateString(),
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);

        // 全ユーザーが表示されること
        $response->assertSee($admin->name);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);

        // 勤怠時間が表示されること
        $response->assertSee('08:00');
        $response->assertSee('17:00');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /**
     * 勤怠一覧画面を開いたとき現在の日付が表示される
     */
    public function test_current_date_is_displayed(): void
    {
        $this->createAdminUser();

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y/m/d'));
    }

    /**
     * 前日の勤怠情報が表示される
     */
    public function test_previous_day_is_displayed(): void
    {
        $this->createAdminUser();

        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $previousDay = now()->subDay();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => $previousDay->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->get(
            '/admin/attendance/list?date='.$previousDay->format('Y-m-d')
        );

        $response->assertStatus(200);

        $response->assertSee($previousDay->format('Y/m/d'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 翌日の勤怠情報が表示される
     */
    public function test_next_day_is_displayed(): void
    {
        $this->createAdminUser();

        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $nextDay = now()->addDay();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => $nextDay->toDateString(),
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->get(
            '/admin/attendance/list?date='.$nextDay->format('Y-m-d')
        );

        $response->assertStatus(200);

        $response->assertSee($nextDay->format('Y/m/d'));
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /**
     * 一般ユーザーは管理者勤怠一覧にアクセスできない
     */
    public function test_general_user_cannot_access_admin_attendance_list(): void
    {
        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get('/admin/attendance/list');

        $response->assertForbidden();
    }
}
