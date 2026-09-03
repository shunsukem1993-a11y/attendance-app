<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーション前に時刻を正規化する。
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'new_clock_in' => $this->normalizeTime(
                $this->input('new_clock_in')
            ),
            'new_clock_out' => $this->normalizeTime(
                $this->input('new_clock_out')
            ),
            'new_break_in' => $this->normalizeTimes(
                $this->input('new_break_in', [])
            ),
            'new_break_out' => $this->normalizeTimes(
                $this->input('new_break_out', [])
            ),
        ]);
    }

    private function normalizeTime(?string $time): ?string
    {
        if (! $time) {
            return $time;
        }

        if (! preg_match('/^\d{1,2}:\d{1,2}$/', $time)) {
            return $time;
        }

        [$hour, $minute] = explode(':', $time);

        return sprintf('%02d:%02d', $hour, $minute);
    }

    private function normalizeTimes(array $times): array
    {
        return array_map(
            fn ($time) => $this->normalizeTime($time),
            $times
        );
    }

    /**
     * バリデーションルールを定義する。
     *
     * @return array<string, mixed> バリデーションルール
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
     * バリデーション後の追加チェックを設定する。
     *
     * @param  Validator  $validator  バリデータ
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $clockIn = $this->input('new_clock_in');
            $clockOut = $this->input('new_clock_out');

            if ($clockIn && $clockOut && $clockIn > $clockOut) {
                $validator->errors()->add(
                    'new_clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です。'
                );
            }

            $breakIns = $this->input('new_break_in', []);
            $breakOuts = $this->input('new_break_out', []);

            foreach ($breakIns as $index => $breakIn) {
                $breakOut = $breakOuts[$index] ?? null;

                if (($breakIn && ! $breakOut) || (! $breakIn && $breakOut)) {
                    $validator->errors()->add(
                        "new_break_in.$index",
                        '休憩時間を開始・終了ともに入力してください。'
                    );

                    continue;
                }

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

                if ($breakOut && $clockOut && $breakOut > $clockOut) {
                    $validator->errors()->add(
                        "new_break_out.$index",
                        '休憩時間もしくは退勤時間が不適切な値です。'
                    );
                }

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
     *
     * @return array<string, string> エラーメッセージ
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
