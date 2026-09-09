<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'nullable|string',
            'email'    => 'nullable|string',
            'password' => 'required|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('username') && !$this->filled('email')) {
                $validator->errors()->add('username', 'Username atau email wajib diisi.');
            }
        });
    }
}
