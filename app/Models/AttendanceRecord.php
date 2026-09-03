<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'comment',
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
     * attendance_record_idを外部キーとしてBreakモデルとのリレーションを定義する。
     *
     * @return HasMany AttendanceBreakとのリレーション
     */
    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    /**
     * attendance_record_idを外部キーとしてAttendanceCorrectionRequestモデルとのリレーションを定義する。
     *
     * @return HasMany AttendanceCorrectionRequestとのリレーション
     */
    public function correctionRequests(): HasMany
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }
}
