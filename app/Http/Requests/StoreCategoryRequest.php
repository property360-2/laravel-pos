<?php

namespace App\Http\Requests;

use App\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $exists = Category::query()
                        ->whereNull('deleted_at')
                        ->whereRaw('LOWER(name) = ?', [Str::lower($value)])
                        ->exists();

                    if ($exists) {
                        $fail('The :attribute has already been taken.');
                    }
                },
            ],
        ];
    }
}
