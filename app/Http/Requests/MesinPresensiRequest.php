<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MesinPresensiRequest extends FormRequest
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
            'nama_mesin' => ['string', 'required'],
            'tipe_koneksi' => ['string'],
            'com_port' => ['string']
        ];
    }
}
