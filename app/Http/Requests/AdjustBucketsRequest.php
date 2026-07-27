<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AdjustBucketsRequest extends FormRequest
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
            'buckets'      => ['array'],
            'buckets.*.id' => [
                'required',
                'integer',
                Rule::exists('savings_buckets', 'id')->where('user_id', $this->user()->id),
            ],
            'buckets.*.real_amount' => ['required', 'integer', 'min:0'],
        ];
    }
}
