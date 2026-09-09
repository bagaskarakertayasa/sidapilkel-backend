<?php

namespace App\Http\Requests\Calon;

use Illuminate\Foundation\Http\FormRequest;

class UploadPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('upload-photo') ?? false;
    }

    public function rules(): array
    {
        return [
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
