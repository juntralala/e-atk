<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:4',
                'max:255',
                'regex:/^[A-Za-z0-9_.]+$/',
                'not_regex:/^[0-9._]/',
                Rule::unique('users', 'username')->ignore(auth()->user()->id),
            ],
            'password' => ['nullable', 'string', 'min:4', 'max:60'],
            'profilePhoto' => ['nullable', 'image', ' mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'telepon' => ['nullable', 'min:10', 'max:20', 'regex:/^08[0-9]+$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'name.min' => 'Nama minimal 4 karakter.',
            'name.max' => 'Nama maksimal 255 karakter.',

            'username.required' => 'Username harus diisi.',
            'username.min' => 'Username minimal 4 karakter.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, titik, dan underscore.',
            'username.not_regex' => 'Username tidak boleh diawali dengan angka, titik, atau underscore.',
            'username.unique' => 'Username sudah digunakan.',

            'password.min' => 'Password minimal 4 karakter.',
            'password.max' => 'Password maksimal 60 karakter.',

            'profile_photo_path.image' => 'File harus berupa gambar.',
            'profile_photo_path.mimes' => 'Format gambar harus jpeg, jpg, png, atau gif.',
            'profile_photo_path.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
