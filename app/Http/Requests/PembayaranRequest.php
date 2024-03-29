<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PembayaranRequest extends FormRequest
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
            'no_cek_bg' => ['string', 'required'],
            'no_rekening' => ['string', 'required'],
            'nama_rekening' => ['string', 'required'],
            'total_setoran' => ['required'],
        ];
    }
}
