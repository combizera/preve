<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TransactionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id'      => ['required', 'integer', 'exists:categories,id'],
            'tag_id'           => ['nullable', 'integer', 'exists:tags,id'],
            'amount'           => ['required', 'numeric'],
            'type'             => ['required', 'in:income,expense'],
            'description'      => ['required', 'string', 'min:3'],
            'notes'            => ['nullable', 'string'],
            'transaction_date' => ['required', 'date'],
        ];
    }
}
