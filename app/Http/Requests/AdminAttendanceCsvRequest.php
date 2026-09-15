<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceCsvRequest extends FormRequest
{
    /**
     * リクエストを実行する権限があるか確認する。
     *
     * @return bool リクエストを許可する場合はtrue
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
