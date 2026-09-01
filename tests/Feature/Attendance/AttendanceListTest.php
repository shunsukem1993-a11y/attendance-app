<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 自分の勤怠情報が全て表示される
     */
    public function test_user_can_see_all_own_attendance_records(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->startOfMonth()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->startOfMonth()->addDays(1)->toDateString(),
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

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
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y/m'));
    }

    /**
     * 前月の情報が表示される
     */
    public function test_previous_month_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $previousMonth = now()->subMonth();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => $previousMonth->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->get(
            '/attendance/list?date='.$previousMonth->format('Y-m')
        );

        $response->assertStatus(200);
        $response->assertSee($previousMonth->format('Y/m'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 翌月の情報が表示される
     */
    public function test_next_month_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $nextMonth = now()->addMonth();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->get('/attendance/list?date='.$nextMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y/m'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 詳細ボタンからその日の勤怠詳細画面へ遷移できる
     */
    public function test_user_can_navigate_to_attendance_detail(): void
    {
        [, $attendanceRecord] = $this->createAttendanceUser();

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

        $response = $this->get(
            '/attendance/detail/'.$attendanceRecord->id
        );

        $response->assertStatus(200);
    }
}
