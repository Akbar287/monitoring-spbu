<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HargaRequest extends FormRequest
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
            'jam_kerja_id' => ['required'],
            'produk_id' => ['required'],
            'tanggal' => ['date', 'required'],
            'harga_jual' => ['required'],
            'harga_beli' => ['required']
        ];
    }
}
