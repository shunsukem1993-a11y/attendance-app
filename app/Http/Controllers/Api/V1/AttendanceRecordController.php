<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use App\Services\AttendanceTimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AttendanceRecordController extends Controller
{
    /**
     * 勤怠一覧を取得する。
     *
     * @param  IndexAttendanceRecordRequest  $request  勤怠一覧の検索条件を含むリクエスト
     * @param  AttendanceTimeService  $attendanceTimeService  勤務時間・休憩時間を計算するサービス
     * @return JsonResponse 勤怠一覧のJSONレスポンス
     */
    public function index(
        IndexAttendanceRecordRequest $request,
        AttendanceTimeService $attendanceTimeService
    ): JsonResponse {
        $validated = $request->validated();

        $perPage = $validated['per_page'] ?? 20;

        $attendanceRecords = AttendanceRecord::with([
            'user',
            'breaks',
        ])
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

        $attendanceRecords->getCollection()->transform(
            function (AttendanceRecord $attendanceRecord) use ($attendanceTimeService) {
                return $attendanceTimeService->calculateAttendanceTimes(
                    $attendanceRecord
                );
            }
        );

        $resource = AttendanceRecordResource::collection(
            $attendanceRecords
        );

        $data = $resource->response()->getData(true);

        $data['data'] = collect($data['data'])
            ->map(function ($record) {
                return [
                    'id' => $record['id'],
                    'user_id' => $record['user_id'],
                    'user_name' => $record['user']['name'] ?? null,
                    'date' => $record['date'],
                    'clock_in' => $record['clock_in'],
                    'clock_out' => $record['clock_out'],
                    'total_time' => $record['total_time'],
                    'total_break_time' => $record['total_break_time'],
                    'comment' => $record['comment'],
                ];
            })
            ->all();

        return response()->json($data);
    }

    /**
     * 指定された勤怠の詳細を取得する。
     *
     * @param  AttendanceRecord  $attendanceRecord  取得対象の勤怠情報
     * @return JsonResponse 勤怠詳細のJSONレスポンス
     */
    public function show(
        AttendanceRecord $attendanceRecord
    ): JsonResponse {
        $attendanceRecord->load([
            'user',
            'breaks',
            'applications',
        ]);

        $resource = new AttendanceRecordResource($attendanceRecord);

        $data = $resource->response()->getData(true);

        unset(
            $data['data']['total_time'],
            $data['data']['total_break_time'],
            $data['data']['user_id']
        );

        return response()->json($data);
    }

    /**
     * 勤怠情報を新規作成する。
     *
     * @param  StoreAttendanceRecordRequest  $request  新規勤怠情報を含むリクエスト
     * @return JsonResponse 作成された勤怠情報のJSONレスポンス
     */
    public function store(
        StoreAttendanceRecordRequest $request
    ): JsonResponse {
        $validated = $request->validated();

        $attendanceRecord = $request->user()
            ->attendanceRecords()
            ->create($validated);

        $attendanceRecord->load([
            'user',
            'breaks',
        ]);

        return (new AttendanceRecordResource($attendanceRecord))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 指定された勤怠情報を更新する。
     *
     * @param  UpdateAttendanceRecordRequest  $request  更新する勤怠情報を含むリクエスト
     * @param  AttendanceRecord  $attendanceRecord  更新対象の勤怠情報
     * @return AttendanceRecordResource 更新された勤怠情報のリソース
     */
    public function update(
        UpdateAttendanceRecordRequest $request,
        AttendanceRecord $attendanceRecord
    ): AttendanceRecordResource {
        $this->authorize('update', $attendanceRecord);

        $attendanceRecord->update($request->validated());

        $attendanceRecord->load([
            'user',
            'breaks',
        ]);

        return new AttendanceRecordResource($attendanceRecord);
    }

    /**
     * 指定された勤怠情報を削除する。
     *
     * @param  AttendanceRecord  $attendanceRecord  削除対象の勤怠情報
     * @return Response 削除成功時の空レスポンス
     */
    public function destroy(AttendanceRecord $attendanceRecord): Response
    {
        $this->authorize('delete', $attendanceRecord);

        $attendanceRecord->delete();

        return response()->noContent();
    }
}
