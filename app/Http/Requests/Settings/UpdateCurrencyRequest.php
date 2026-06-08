<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Enums\SupportedCurrency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class UpdateCurrencyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'currency' => ['required', 'string', new Enum(SupportedCurrency::class)],
        ];
    }
}
