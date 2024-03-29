<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileLoRequest extends FormRequest
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
            'detail_rencana_pembelian_id' => ['required'],
            'no_faktur' => ['required'],
            'no_lo' => ['required'],
        ];
    }
}
