<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PajakPembelianLibRequest extends FormRequest
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
            'ppn' => ['required'],
            'ppbkb' => ['required'],
            'pph' => ['required'],
            'is_aktif' => ['required']
        ];
    }
}
