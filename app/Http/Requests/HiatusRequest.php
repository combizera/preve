<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Hiatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Validator;

final class HiatusRequest extends FormRequest
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
            'started_at' => ['required', 'date'],
            'ended_at'   => ['nullable', 'date', 'after_or_equal:started_at'],
            'note'       => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $start = Date::parse($this->string('started_at')->value());
                $end = $this->filled('ended_at') ? Date::parse($this->string('ended_at')->value()) : null;

                $overlaps = $this->user()
                    ->hiatuses()
                    ->overlapping($start, $end)
                    ->when(
                        $this->route('hiatus') instanceof Hiatus,
                        fn ($query) => $query->whereKeyNot($this->route('hiatus')->id),
                    )
                    ->exists();

                if ($overlaps) {
                    $validator->errors()->add('started_at', __('validation.custom.hiatus.overlaps'));
                }
            },
        ];
    }
}
