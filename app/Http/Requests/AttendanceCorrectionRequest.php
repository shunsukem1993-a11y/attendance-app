<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * リクエストの認可を行う。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルールを定義する。
     */
    public function rules(): array
    {
        return [
            'new_clock_in' => [
                'required',
                'date_format:H:i',
            ],

            'new_clock_out' => [
                'required',
                'date_format:H:i',
            ],

            'new_break_in' => [
                'nullable',
                'array',
            ],

            'new_break_in.*' => [
                'nullable',
                'date_format:H:i',
            ],

            'new_break_out' => [
                'nullable',
                'array',
            ],

            'new_break_out.*' => [
                'nullable',
                'date_format:H:i',
            ],

            'comment' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * バリデーション後の追加チェックを行う。
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('new_clock_in');
            $clockOut = $this->input('new_clock_out');

            /*
            * 出勤・退勤の前後関係を確認する。
            */
            if ($clockIn && $clockOut && $clockIn > $clockOut) {
                $validator->errors()->add(
                    'new_clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です。'
                );
            }

            /*
            * 休憩時間の前後関係を確認する。
            */
            $breakIns = $this->input('new_break_in', []);
            $breakOuts = $this->input('new_break_out', []);

            foreach ($breakIns as $index => $breakIn) {
                $breakOut = $breakOuts[$index] ?? null;

                /*
                * 休憩開始・終了はセットで入力する。
                */
                if (($breakIn && ! $breakOut) || (! $breakIn && $breakOut)) {
                    $validator->errors()->add(
                        "new_break_in.$index",
                        '休憩時間を開始・終了ともに入力してください。'
                    );

                    continue;
                }

                /*
                * 休憩開始時間が出勤より前、
                * または退勤より後の場合。
                */
                if (
                    $breakIn
                    && $clockIn
                    && $clockOut
                    && ($breakIn < $clockIn || $breakIn > $clockOut)
                ) {
                    $validator->errors()->add(
                        "new_break_in.$index",
                        '休憩時間が不適切な値です。'
                    );
                }

                /*
                * 休憩終了時間が退勤より後の場合。
                */
                if ($breakOut && $clockOut && $breakOut > $clockOut) {
                    $validator->errors()->add(
                        "new_break_out.$index",
                        '休憩時間もしくは退勤時間が不適切な値です。'
                    );
                }

                /*
                * 休憩終了時間が休憩開始より前の場合。
                */
                if ($breakIn && $breakOut && $breakOut < $breakIn) {
                    $validator->errors()->add(
                        "new_break_out.$index",
                        '休憩時間が不適切な値です。'
                    );
                }
            }
        });
    }

    /**
     * バリデーションエラーメッセージを定義する。
     */
    public function messages(): array
    {
        return [
            'new_clock_in.required' => '出勤時間を入力してください。',
            'new_clock_in.date_format' => '出勤時間は正しい形式で入力してください。',

            'new_clock_out.required' => '退勤時間を入力してください。',
            'new_clock_out.date_format' => '退勤時間は正しい形式で入力してください。',

            'new_break_in.*.date_format' => '休憩時間は正しい形式で入力してください。',

            'new_break_out.*.date_format' => '休憩時間は正しい形式で入力してください。',

            'comment.required' => '備考を記入してください。',
            'comment.string' => '備考は文字列で入力してください。',
            'comment.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
