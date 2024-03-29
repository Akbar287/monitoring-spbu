<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserloginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['string', 'required'],
            'password' => ['string', 'required'],
            'is_aktif' => ['boolean', 'required'],
            'access_token' => ['string'],
            'access' => ['string'],
            'last_login' => ['date']
        ];
    }
}
