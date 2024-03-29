<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FasilitasRequest extends FormRequest
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
            'nama_fasilitas' => ['string', 'required'],
            'jumlah' => ['integer', 'required'],
            'tanggal_digunakan' => ['date', 'required'],
            'keterangan' => ['string', 'required']
        ];
    }
}
