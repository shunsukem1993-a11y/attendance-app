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
            for ($i = 0; $i <= 20; $i++) {
                $date = Carbon::now()->subDays($i);

                // 土日は除外
                if ($date->isWeekend()) {
                    continue;
                }

                AttendanceRecord::create([
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                    'clock_in' => '09:00:00',
                    'clock_out' => '18:00:00',
                    'comment' => '通常勤務',
                ]);
            }
        }
    }
}
