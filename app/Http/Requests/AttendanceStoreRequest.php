<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
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
