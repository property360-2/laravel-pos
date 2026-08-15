<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.store_name' => ['required_with:settings', 'string', 'max:255'],
            'settings.store_address' => ['nullable', 'string', 'max:255'],
            'settings.store_phone' => ['nullable', 'string', 'max:50'],
            'settings.currency_symbol' => ['nullable', 'string', 'max:5'],
            'settings.receipt_footer' => ['nullable', 'string', 'max:255'],
        ];
    }
}
