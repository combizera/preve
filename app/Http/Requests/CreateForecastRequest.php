<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateForecastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('user_id', $this->user()->id)
                    ->where('type', TransactionType::EXPENSE->value),
                Rule::unique('forecast_series', 'category_id')
                    ->where('user_id', $this->user()->id),
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'month'  => ['required', 'date'],
            'notes'  => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.unique' => __('validation.custom.forecast.series_already_exists'),
        ];
    }
}
