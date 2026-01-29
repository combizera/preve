<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class RecurringTransactionRequest extends FormRequest
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
            'category_id'  => ['required', 'integer', 'exists:categories,id'],
            'tag_id'       => ['nullable', 'integer', 'exists:tags,id'],
            'amount'       => ['required', 'numeric'],
            'type'         => ['required', 'in:income,expense'],
            'frequency'    => ['required', 'in:monthly,yearly'],
            'description'  => ['required', 'string', 'min:3'],
            'day_of_month' => ['required', 'integer', 'min:1', 'max:31'],
            'start_date'   => ['required', 'date'],
            'end_date'     => ['nullable', 'date', 'after:start_date'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $category = Category::query()->find($this->category_id);

                if ($category && $category->type->value !== $this->type) {
                    $validator->errors()->add(
                        'category_id',
                        "The selected category must be of type {$this->type}."
                    );
                }
            },
        ];
    }
}
