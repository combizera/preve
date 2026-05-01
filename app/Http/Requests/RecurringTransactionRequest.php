<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Throwable;

final class RecurringTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'category_id'  => ['required', 'integer', Rule::exists('categories', 'id')->where('user_id', $this->user()->id)],
            'tags'         => ['nullable', 'array'],
            'tags.*'       => ['integer', Rule::exists('tags', 'id')->where('user_id', $this->user()->id)],
            'amount'       => ['required', 'numeric', 'min:1'],
            'type'         => ['required', 'in:income,expense'],
            'frequency'    => ['required', 'in:monthly,yearly'],
            'description'  => ['required', 'string', 'min:3'],
            'is_active'    => ['boolean'],
            'day_of_month' => ['required_if:frequency,monthly', 'nullable', 'integer', 'min:1', 'max:31'],
            'start_date'   => ['required', 'date'],
            'end_date'     => ['nullable', 'date', 'after:start_date'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $category = $this->user()->categories()->find($this->category_id);

                if ($category && $category->type->value !== $this->type) {
                    $validator->errors()->add(
                        'category_id',
                        __('validation.custom.category_id.type_mismatch', ['type' => $this->type]),
                    );
                }
            },
        ];
    }

    /**
     * Yearly recurrences ignore the user-supplied day_of_month — the day part
     * is fully encoded by start_date. Normalize the input before validation
     * so the merged value is what ends up in validated().
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('frequency') !== 'yearly') {
            return;
        }

        $startDate = $this->input('start_date');

        if (!is_string($startDate)) {
            return;
        }

        try {
            $day = Date::parse($startDate)->day;
        } catch (Throwable) {
            return;
        }

        $this->merge(['day_of_month' => $day]);
    }
}
