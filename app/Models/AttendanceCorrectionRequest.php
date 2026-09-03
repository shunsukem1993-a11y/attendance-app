<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 0;

    public const STATUS_APPROVED = 1;

    protected $fillable = [
        'user_id',
        'attendance_record_id',
        'approval_status',
        'comment',
        'new_date',
        'new_clock_in',
        'new_clock_out',
    ];

    protected $casts = [
        'new_date' => 'date',
    ];

    /**
     * user_idを外部キーとしてUserモデルとのリレーションを定義する。
     *
     * @return BelongsTo Userとのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * attendance_record_idを外部キーとしてAttendanceRecordモデルとのリレーションを定義する。
     *
     * @return BelongsTo AttendanceRecordとのリレーション
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * 修正申請に紐づく申請休憩情報とのリレーションを定義する。
     *
     * @return HasMany ProposalBreakとのリレーション
     */
    public function proposalBreaks(): HasMany
    {
        return $this->hasMany(ProposalBreak::class);
    }
}
