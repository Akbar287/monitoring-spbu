<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GajiRequest extends FormRequest
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
            'tanggal_berlaku' => ['date', 'required'],
            'total_gaji' => ['numeric', 'required'],
            'potongan_pajak' => ['numeric', 'required'],
            'keterangan' => ['string'],
            'is_aktif' => ['boolean', 'required']
        ];
    }
}
