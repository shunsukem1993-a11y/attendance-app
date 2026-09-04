<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceCsvRequest extends FormRequest
{
    /**
     * リクエストの認可を判定する。
     *
     * @return bool 認可結果
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルールを取得する。
     *
     * @return array<string, string> バリデーションルール
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'year_month' => [
                'required',
                'date_format:Y-m',
            ],
        ];
    }
}
