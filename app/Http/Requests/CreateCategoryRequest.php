<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\GeneratesUniqueSlug;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

final class CreateCategoryRequest extends FormRequest
{
    use GeneratesUniqueSlug;

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
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', 'unique:categories,slug,NULL,id,user_id,' . $this->user()->id],
            'type'        => ['required', 'in:income,expense'],
            'description' => ['nullable', 'string'],
            'color'       => ['string', 'max:10'],
            'icon'        => ['string', 'max:255'],
        ];
    }

    /**
     * Get the model class for slug uniqueness check.
     */
    protected function getModelClass(): string
    {
        return Category::class;
    }
}
