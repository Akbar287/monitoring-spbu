<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AreaMemberRequest extends FormRequest
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
            'provinsi' => ['string', 'required'],
            'kabupaten' => ['string', 'required'],
            'kecamatan' => ['string', 'required'],
            'desa' => ['string', 'required'],
            'rw' => ['string', 'required'],
            'rt' => ['string', 'required'],
            'no' => ['string', 'required'],
            'alamat' => ['string', 'required'],
            'is_primary' => ['boolean', 'required']
        ];
    }
}
