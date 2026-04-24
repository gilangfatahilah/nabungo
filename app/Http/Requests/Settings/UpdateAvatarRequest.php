<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Please select an image to upload.',
            'avatar.image'    => 'The file must be an image.',
            'avatar.mimes'    => 'Only JPEG, PNG, JPG, GIF, and WebP images are allowed.',
            'avatar.max'      => 'The image size must not exceed 2MB.',
        ];
    }
}
