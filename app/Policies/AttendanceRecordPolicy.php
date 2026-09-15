<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;

class AttendanceRecordPolicy
{
    /**
     * ユーザーが勤怠一覧を閲覧できるか確認する。
     *
     * @param  User  $user  操作を行うユーザー
     * @return bool 閲覧を許可する場合はtrue
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * ユーザーが指定された勤怠を閲覧できるか確認する。
     *
     * @param  User  $user  操作を行うユーザー
     * @param  AttendanceRecord  $attendanceRecord  対象の勤怠情報
     * @return bool 閲覧を許可する場合はtrue
     */
    public function view(
        User $user,
        AttendanceRecord $attendanceRecord
    ): bool {
        //
    }

    /**
     * ユーザーが勤怠を作成できるか確認する。
     *
     * @param  User  $user  操作を行うユーザー
     * @return bool 作成を許可する場合はtrue
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * ユーザーが指定された勤怠を更新できるか確認する。
     *
     * @param  User  $user  操作を行うユーザー
     * @param  AttendanceRecord  $attendanceRecord  対象の勤怠情報
     * @return bool 更新を許可する場合はtrue
     */
    public function update(
        User $user,
        AttendanceRecord $attendanceRecord
    ): bool {
        return $user->id === $attendanceRecord->user_id;
    }

    /**
     * ユーザーが指定された勤怠を削除できるか確認する。
     *
     * @param  User  $user  操作を行うユーザー
     * @param  AttendanceRecord  $attendanceRecord  対象の勤怠情報
     * @return bool 削除を許可する場合はtrue
     */
    public function delete(
        User $user,
        AttendanceRecord $attendanceRecord
    ): bool {
        return $user->id === $attendanceRecord->user_id;
    }

    /**
     * ユーザーが指定された勤怠を復元できるか確認する。
     *
     * @param  User  $user  操作を行うユーザー
     * @param  AttendanceRecord  $attendanceRecord  対象の勤怠情報
     * @return bool 復元を許可する場合はtrue
     */
    public function restore(
        User $user,
        AttendanceRecord $attendanceRecord
    ): bool {
        //
    }

    /**
     * ユーザーが指定された勤怠を完全削除できるか確認する。
     *
     * @param  User  $user  操作を行うユーザー
     * @param  AttendanceRecord  $attendanceRecord  対象の勤怠情報
     * @return bool 完全削除を許可する場合はtrue
     */
    public function forceDelete(
        User $user,
        AttendanceRecord $attendanceRecord
    ): bool {
        //
    }

    /**
     * 管理者の場合はすべての操作を許可する。
     *
     * @param  User  $user  操作を行うユーザー
     * @param  string  $ability  実行しようとしている権限
     * @return ?bool 管理者の場合はtrue、通常の権限判定に委ねる場合はnull
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->admin_status) {
            return true;
        }

        return null;
    }
}
