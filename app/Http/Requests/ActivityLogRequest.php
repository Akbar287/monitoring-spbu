<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivityLogRequest extends FormRequest
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
            'nama_aktivitas' => ['string', 'required'],
            'class' => ['string', 'required'],
            'function' => ['string', 'required'],
            'input' => ['string', 'required'],
            'output' => ['string', 'required']
        ];
    }
}
