<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BulkAssignCreditCardRequest extends FormRequest
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
            'ids'            => ['required', 'array', 'min:1'],
            'ids.*'          => ['string', Rule::exists('transactions', 'id')->where('user_id', $this->user()->id)],
            'credit_card_id' => ['required', 'integer', Rule::exists('credit_cards', 'id')->where('user_id', $this->user()->id)],
        ];
    }
}
