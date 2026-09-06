<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalBreakSeconds = $this->breaks
            ->filter(function ($break): bool {
                return $break->break_in && $break->break_out;
            })
            ->sum(function ($break): int {
                $breakIn = Carbon::parse($break->break_in);
                $breakOut = Carbon::parse($break->break_out);

                return $breakIn->diffInSeconds($breakOut);
            });

        $totalTime = null;

        if ($this->clock_in && $this->clock_out) {
            $clockIn = Carbon::parse($this->clock_in);
            $clockOut = Carbon::parse($this->clock_out);

            $workSeconds = $clockIn->diffInSeconds($clockOut)
                - $totalBreakSeconds;

            $totalTime = sprintf(
                '%02d:%02d',
                intdiv($workSeconds, 3600),
                intdiv($workSeconds % 3600, 60)
            );
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user->name,
            'date' => $this->date,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,
            'total_time' => $totalTime,
            'total_break_time' => sprintf(
                '%02d:%02d',
                intdiv($totalBreakSeconds, 3600),
                intdiv($totalBreakSeconds % 3600, 60)
            ),
            'comment' => $this->comment,
        ];
    }
}
