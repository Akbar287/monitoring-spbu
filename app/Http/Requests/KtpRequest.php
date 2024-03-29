<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KtpRequest extends FormRequest
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
            'verified' => ['boolean', 'required']
        ];
    }
}
