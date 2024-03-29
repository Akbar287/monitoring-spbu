<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JamKerjaRequest extends FormRequest
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
            'nama_jam_kerja' => ['string', 'required'],
            "batas_awal_in" => ['required'],
            "batas_akhir_in" => ['required'],
            "jam_datang_in" => ['required'],
            "jam_pulang_out" => ['required'],
            "akhir_out" => ['required'],
            "jam_istirahat" => ['required'],
            "masuk_istirahat" => ['required'],
            'batas_telat' => ['required'],
            'urutan' => ['required'],
        ];
    }
}
