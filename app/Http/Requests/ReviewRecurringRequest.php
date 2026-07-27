<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReviewRecurringRequest extends FormRequest
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
            'delete_transaction_ids'   => ['array'],
            'delete_transaction_ids.*' => [
                'string',
                'uuid',
                Rule::exists('transactions', 'id')
                    ->where('user_id', $this->user()->id)
                    ->whereNotNull('recurring_transaction_id'),
            ],
            'deactivate_recurring_ids'   => ['array'],
            'deactivate_recurring_ids.*' => [
                'string',
                'uuid',
                Rule::exists('recurring_transactions', 'id')
                    ->where('user_id', $this->user()->id),
            ],
        ];
    }
}
