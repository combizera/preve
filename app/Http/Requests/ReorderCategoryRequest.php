<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReorderCategoryRequest extends FormRequest
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
        $userId = $this->user()->id;
        $type = $this->input('type');

        return [
            'type'  => ['required', 'in:income,expense'],
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('categories', 'id')->where(function (Builder $query) use ($userId, $type): void {
                    $query->where('user_id', $userId);

                    if (in_array($type, ['income', 'expense'], true)) {
                        $query->where('type', $type);
                    }
                }),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $expected = Category::query()
                ->where('user_id', $this->user()->id)
                ->where('type', $this->input('type'))
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->sort()
                ->values()
                ->all();

            $submitted = collect($this->input('ids', []))
                ->map(fn ($id): int => (int) $id)
                ->sort()
                ->values()
                ->all();

            if ($expected !== $submitted) {
                $validator->errors()->add('ids', __('validation.custom.categories.reorder_full_set'));
            }
        });
    }
}
