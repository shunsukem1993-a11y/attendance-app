<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

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

    /**
     * 管理者以外は管理者勤怠詳細にアクセスできない
     */
    public function test_general_user_cannot_access_admin_attendance_detail(): void
    {
        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(
            '/admin/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertForbidden();
    }

    /**
     * 管理者はスタッフ一覧に表示されない
     */
    public function test_admin_is_not_displayed_in_staff_list(): void
    {
        $admin = $this->createAdminUser();

        $user = User::factory()->create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'admin_status' => false,
        ]);

        $response = $this->get('/admin/staff/list');

        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee($user->email);

        $response->assertDontSee($admin->name);
        $response->assertDontSee($admin->email);
    }

    /**
     * 一般ユーザーは管理者スタッフ一覧にアクセスできない
     */
    public function test_general_user_cannot_access_admin_staff_list(): void
    {
        $user = User::factory()->create([
            'admin_status' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get('/admin/staff/list');

        $response->assertForbidden();
    }
}
