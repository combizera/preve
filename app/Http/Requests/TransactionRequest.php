<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id'      => ['required', 'integer', Rule::exists('categories', 'id')->where('user_id', $this->user()->id)],
            'tags'             => ['nullable', 'array'],
            'tags.*'           => ['integer', Rule::exists('tags', 'id')->where('user_id', $this->user()->id)],
            'amount'           => ['required', 'numeric', 'min:1'],
            'type'             => ['required', 'in:income,expense'],
            'description'      => ['required', 'string', 'min:3'],
            'notes'            => ['nullable', 'string'],
            'transaction_date' => ['required', 'date'],
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
}
