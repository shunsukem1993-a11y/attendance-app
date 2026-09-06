<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AttendanceRecordController extends Controller
{
    /**
     * 勤怠一覧を取得する。
     *
     * 指定されたユーザー、日付、月で勤怠情報を絞り込み、
     * ページネーション付きで返す。
     */
    public function index(IndexAttendanceRecordRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $perPage = $validated['per_page'] ?? 20;

        $attendanceRecords = AttendanceRecord::with(['user', 'breaks'])
            ->when(
                $validated['user_id'] ?? null,
                function ($query, $userId) {
                    $query->where('user_id', $userId);
                }
            )
            ->when(
                $validated['date'] ?? null,
                function ($query, $date) {
                    $query->where('date', $date);
                }
            )
            ->when(
                $validated['month'] ?? null,
                function ($query, $month) {
                    $query->where('date', 'like', $month.'%');
                }
            )
            ->latest('date')
            ->paginate($perPage);

        return AttendanceRecordResource::collection($attendanceRecords);
    }
}
