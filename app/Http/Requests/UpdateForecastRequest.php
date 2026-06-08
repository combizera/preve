<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateForecastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount'           => ['required', 'integer', 'min:1'],
            'notes'            => ['nullable', 'string'],
            'apply_to_default' => ['nullable', 'boolean'],
        ];
    }
}
