<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PenarikanRequest extends FormRequest
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
            'nama_bank' => ['string', 'required'],
            'no_referensi' => ['string', 'required'],
            'metode_transfer' => ['string', 'required'],
            'is_accepted' => ['boolean', 'required']
        ];
    }
}
