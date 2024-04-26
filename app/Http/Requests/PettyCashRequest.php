<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PettyCashRequest extends FormRequest
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
            'no_kode' => ['string', 'required', 'size:4'],
            'no_bukti' => ['string', 'required'],
            'keterangan' => ['string', 'required'],
            'jumlah' => ['required'],
            'jenis' => ['required'],
            'tanggal' => ['date', 'required']
        ];
    }
}
