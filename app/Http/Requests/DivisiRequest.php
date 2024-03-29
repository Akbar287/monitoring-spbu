<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DivisiRequest extends FormRequest
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
            'spbu_id' => ['required'],
            'nama_divisi' => ['string', 'required'],
            'keterangan' => ['string'],
            'is_aktif' => ['required']
        ];
    }
}
