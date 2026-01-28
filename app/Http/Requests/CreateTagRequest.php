<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\GeneratesUniqueSlug;
use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;

final class CreateTagRequest extends FormRequest
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
            'slug'        => ['required', 'string', 'max:255', 'unique:tags,slug,NULL,id,user_id,' . $this->user()->id],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the model class for slug uniqueness check.
     */
    protected function getModelClass(): string
    {
        return Tag::class;
    }
}
