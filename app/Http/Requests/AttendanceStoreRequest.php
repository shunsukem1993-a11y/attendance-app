<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceStoreRequest extends FormRequest
{
    /**
     * リクエストを許可するか。
     *
     * @return bool リクエストを許可する場合はtrue
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルールを定義する。
     *
     * @return array<string, mixed> バリデーションルール
     */
    public function rules(): array
    {
        return [
            'action' => [
                'required',
                'in:clock_in,clock_out,break_in,break_out',
            ],
        ];
    }
}
