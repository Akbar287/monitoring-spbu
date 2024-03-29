<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StandMeterRequest extends FormRequest
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
            'nozzle_id' => ['required'],
            'jam_kerja_id' => ['required'],
            'dombak_id' => ['required'],
            'tanggal' => ['date', 'required'],
        ];
    }
}
