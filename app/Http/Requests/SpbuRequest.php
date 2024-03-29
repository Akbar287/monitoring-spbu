<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpbuRequest extends FormRequest
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
            'nama_spbu' => ['string', 'required'],
            'nomor_spbu' => ['string', 'required'],
            'tanggal_pendirian' => ['date', 'required'],
            'luas_lahan' => ['required'],
            'satuan_luas' => ['string', 'required'],
            'alamat' => ['string', 'required']
        ];
    }
}
