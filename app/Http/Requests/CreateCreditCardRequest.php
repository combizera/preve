<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\AccentColor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateCreditCardRequest extends FormRequest
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
            'name'         => ['required', 'string', 'max:255'],
            'last_four'    => ['nullable', 'string', 'digits:4'],
            'closing_day'  => ['required', 'integer', 'min:1', 'max:31'],
            'due_day'      => ['required', 'integer', 'min:1', 'max:31'],
            'color'        => ['required', 'string', Rule::enum(AccentColor::class)],
            'credit_limit' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
