<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PresensiRequest extends FormRequest
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
            'tanggal' => ['date', 'required'],
            'waktu_datang' => ['date', 'required'],
            'waktu_selesai' => ['date'],
            'is_verified' => ['boolean', 'required'],
            'is_aktif' => ['boolean', 'required'],
            'terlambat' => ['date'],
            'keterangan' => ['string']
        ];
    }
}
