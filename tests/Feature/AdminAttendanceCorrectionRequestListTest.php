<?php

namespace Tests\Feature;

use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CorrectionRequestTestHelper;
use Tests\TestCase;

class AdminAttendanceCorrectionRequestListTest extends TestCase
{
    use CorrectionRequestTestHelper;
    use RefreshDatabase;

    /**
     * 管理者が承認待ち申請を全て確認できる
     */
    public function test_pending_applications_are_displayed(): void
    {
        $this->createAdminUser();

        $user1 = User::factory()->create([
            'name' => '一般ユーザー1',
        ]);

        $user2 = User::factory()->create([
            'name' => '一般ユーザー2',
        ]);

        $attendanceRecord1 = AttendanceRecord::factory()->create([
            'user_id' => $user1->id,
            'date' => '2026-09-01',
        ]);

        $attendanceRecord2 = AttendanceRecord::factory()->create([
            'user_id' => $user2->id,
            'date' => '2026-09-02',
        ]);

        $this->createCorrectionRequest(
            $user1,
            $attendanceRecord1,
            AttendanceCorrectionRequest::STATUS_PENDING,
            [
                'comment' => '申請コメント1',
            ]
        );

        $this->createCorrectionRequest(
            $user2,
            $attendanceRecord2,
            AttendanceCorrectionRequest::STATUS_PENDING,
            [
                'comment' => '申請コメント2',
            ]
        );

        $response = $this->get('/stamp_correction_request/list');

        $response->assertOk();
        $response->assertSee('承認待ち');
        $response->assertSee('一般ユーザー1');
        $response->assertSee('一般ユーザー2');
        $response->assertSee('申請コメント1');
        $response->assertSee('申請コメント2');
    }

    /**
     * 管理者が承認済み申請を全て確認できる
     */
    public function test_approved_applications_are_displayed(): void
    {
        $this->createAdminUser();

        $user1 = User::factory()->create([
            'name' => '承認済みユーザー1',
        ]);

        $user2 = User::factory()->create([
            'name' => '承認済みユーザー2',
        ]);

        $attendanceRecord1 = AttendanceRecord::factory()->create([
            'user_id' => $user1->id,
        ]);

        $attendanceRecord2 = AttendanceRecord::factory()->create([
            'user_id' => $user2->id,
        ]);

        $this->createCorrectionRequest(
            $user1,
            $attendanceRecord1,
            AttendanceCorrectionRequest::STATUS_APPROVED,
            [
                'comment' => '承認済みコメント1',
            ]
        );

        $this->createCorrectionRequest(
            $user2,
            $attendanceRecord2,
            AttendanceCorrectionRequest::STATUS_APPROVED,
            [
                'comment' => '承認済みコメント2',
            ]
        );

        $response = $this->get('/stamp_correction_request/list');

        $response->assertOk();
        $response->assertSee('承認済み');
        $response->assertSee('承認済みユーザー1');
        $response->assertSee('承認済みユーザー2');
        $response->assertSee('承認済みコメント1');
        $response->assertSee('承認済みコメント2');
    }
}
