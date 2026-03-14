<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCreateRequest extends FormRequest
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
            'name' => ['required', 'min:4', 'max:255'],
            'username' => ['required', 'min:4', 'max:255', 'regex:/^[A-Za-z0-9_.]+$/', 'not_regex:/^[0-9._]/', 'unique:users,username'],
            'password' => ['required', 'min:4', 'max:60'],
            'role' => ['required', Rule::exists('roles', 'id')],
            'telepon' => ['nullable', 'string', 'min:4', 'max:60'],
        ];
    }
}
