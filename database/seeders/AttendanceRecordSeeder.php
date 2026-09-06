<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($user->email === 'user1@example.com') {
                $this->createUser1Records($user);
            } else {
                $this->createNormalRecords($user);
            }
        }
    }

    /**
     * user1の検証用ダミーデータを作成する。
     */
    private function createUser1Records(User $user): void
    {
        // 過去5ヶ月：各月15営業日
        for ($month = 5; $month >= 1; $month--) {
            $this->createPastMonthRecords(
                $user,
                now()->subMonths($month)
            );
        }

        // 今月：17日
        $this->createCurrentMonthRecords($user);
    }

    /**
     * 過去1ヶ月分の通常勤務データを15営業日分作成する。
     */
    private function createPastMonthRecords(
        User $user,
        Carbon $month
    ): void {
        $date = $month->copy()->startOfMonth();
        $createdDays = 0;

        while ($createdDays < 15) {
            if ($date->isWeekday()) {
                $this->createRecord(
                    $user,
                    $date,
                    '09:00:00',
                    '18:00:00',
                    '通常勤務'
                );

                $createdDays++;
            }

            $date->addDay();
        }
    }

    /**
     * 今月の検証用データを17日分作成する。
     */
    private function createCurrentMonthRecords(User $user): void
    {
        $date = now()->startOfMonth();
        $createdDays = 0;

        while ($createdDays < 17) {
            if ($date->isWeekday()) {
                if ($createdDays < 10) {
                    // 通常勤務：10日
                    $clockIn = '09:00:00';
                    $clockOut = '18:00:00';
                    $comment = '通常勤務';
                } elseif ($createdDays < 13) {
                    // 残業：3日
                    $clockIn = '09:00:00';
                    $clockOut = '20:00:00';
                    $comment = '残業勤務';
                } elseif ($createdDays < 15) {
                    // 遅刻：2日
                    $clockIn = '09:30:00';
                    $clockOut = '18:00:00';
                    $comment = '遅刻勤務';
                } elseif ($createdDays < 16) {
                    // 早退：1日
                    $clockIn = '09:00:00';
                    $clockOut = '17:00:00';
                    $comment = '早退勤務';
                } else {
                    // 長時間労働：1日
                    $clockIn = '08:00:00';
                    $clockOut = '21:00:00';
                    $comment = '長時間勤務';
                }

                $this->createRecord(
                    $user,
                    $date,
                    $clockIn,
                    $clockOut,
                    $comment
                );

                $createdDays++;
            }

            $date->addDay();
        }
    }

    /**
     * 通常ユーザー用のダミーデータを作成する。
     */
    private function createNormalRecords(User $user): void
    {
        for ($i = 0; $i < 20; $i++) {
            $date = now()->subDays($i);

            if ($date->isWeekend()) {
                continue;
            }

            $this->createRecord(
                $user,
                $date,
                '09:00:00',
                '18:00:00',
                '通常勤務'
            );
        }
    }

    /**
     * 勤怠記録を1件作成する。
     */
    private function createRecord(
        User $user,
        Carbon $date,
        string $clockIn,
        string $clockOut,
        string $comment
    ): void {
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'comment' => $comment,
        ]);
    }
}
