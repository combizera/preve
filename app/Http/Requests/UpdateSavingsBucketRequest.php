<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\AccentColor;
use App\Enums\SavingsBucketIcon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSavingsBucketRequest extends FormRequest
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
            'name'          => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'color'         => ['required', 'string', Rule::enum(AccentColor::class)],
            'icon'          => ['required', 'string', Rule::enum(SavingsBucketIcon::class)],
        ];
    }
}
