<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoaRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $coa = $this->route('coa');

        return [
            'kode_akun' => ['required', 'string', 'max:16', Rule::unique('coa', 'kode_akun')->ignore($coa?->coa_id, 'coa_id')],
            'nama_akun' => ['required', 'string', 'max:128'],
            'tipe_akun' => ['required', Rule::in(\App\Models\Coa::TIPE_AKUN)],
            'saldo_normal' => ['required', Rule::in(['debit', 'kredit'])],
            'kategori_arus_kas' => ['nullable', Rule::in(['operasi', 'investasi', 'pendanaan'])],
            'parent_coa_id' => ['nullable', 'uuid', Rule::exists('coa', 'coa_id')],
            'is_kas' => ['nullable'],
            'is_header' => ['nullable'],
            'is_aktif' => ['nullable'],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
