<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ToggleStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminPusat() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:aktif,nonaktif',
        ];
    }
}
