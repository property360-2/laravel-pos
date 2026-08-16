<?php

namespace App\Http\Requests;

use App\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($categoryId): void {
                    $exists = Category::query()
                        ->whereNull('deleted_at')
                        ->whereRaw('LOWER(name) = ?', [Str::lower($value)])
                        ->when($categoryId, fn ($query) => $query->where('id', '!=', $categoryId))
                        ->exists();

                    if ($exists) {
                        $fail('The :attribute has already been taken.');
                    }
                },
            ],
        ];
    }
}
