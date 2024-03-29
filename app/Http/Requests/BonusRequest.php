<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BonusRequest extends FormRequest
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
            'persentase' => ['integer', 'required'],
            'total_bonus' => ['numeric', 'required'],
            'tanggal' => ['date', 'required'],
            'deksripsi' => ['string']
        ];
    }
}
