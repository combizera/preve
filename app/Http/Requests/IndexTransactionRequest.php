<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

final class IndexTransactionRequest extends FormRequest
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
            'search'       => ['nullable', 'string', 'max:255'],
            'type'         => ['nullable', 'string', 'in:income,expense'],
            'date_start'   => ['nullable', 'date'],
            'date_end'     => ['nullable', 'date', 'after_or_equal:date_start'],
            'categories'   => ['nullable', 'array'],
            'categories.*' => [
                'integer',
                Rule::exists('categories', 'id')->where('user_id', Auth::id()),
            ],
            'tags'   => ['nullable', 'array'],
            'tags.*' => [
                'integer',
                Rule::exists('tags', 'id')->where('user_id', Auth::id()),
            ],
        ];
    }
}
