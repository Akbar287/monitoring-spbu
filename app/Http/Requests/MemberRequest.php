<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MemberRequest extends FormRequest
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
            'nik' => ['string', 'required'],
            'nama' => ['string', 'required'],
            'tempat_lahir' => ['string', 'required'],
            'tanggal_lahir' => ['date', 'required'],
            'email' => ['string', 'required', 'unique:member,email'],
            'no_hp' => ['string'],
            'no_wa' => ['string'],
            'bergabung_sejak' => ['date', 'required'],
            'username' => ['string', 'required'],
            'password' => ['string', 'required'],
        ];
    }
}
