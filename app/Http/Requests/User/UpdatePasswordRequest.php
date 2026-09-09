<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminPusat() ?? false;
    }

    public function rules(): array
    {
        return [
            'password' => 'required|string|min:6',
        ];
    }
}
