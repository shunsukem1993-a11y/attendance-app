<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceBreak extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'attendance_record_id',
        'break_in',
        'break_out',
    ];

    /**
     * attendance_record_idを外部キーとしてAttendanceRecordモデルとのリレーションを定義する。
     *
     * @return BelongsTo AttendanceRecordとのリレーション
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}
