<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;

final class IndexForecastRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'month' => ['nullable', 'string', 'date_format:Y-m'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('month')) {
            $this->merge(['month' => Date::now()->format('Y-m')]);
        }
    }
}
