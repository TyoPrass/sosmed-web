<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'username' => 'sometimes|string|max:255|unique:users,username,' . auth()->id() . ',_id',
            'email' => 'sometimes|email|unique:users,email,' . auth()->id() . ',_id',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }
}