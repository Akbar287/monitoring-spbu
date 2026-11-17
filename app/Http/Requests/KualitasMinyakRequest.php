<?php

namespace App\Http\Requests;

use App\Models\KualitasMinyak;
use App\Support\KualitasMinyakAkses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KualitasMinyakRequest extends FormRequest
{
    public function authorize(): bool
    {
        $record = $this->route('kualitasMinyak');

        return $record instanceof KualitasMinyak
            ? ! $record->disetujui_pada && ($record->dibuat_oleh === $this->user()->getKey() || KualitasMinyakAkses::menyetujui($this->user()))
            : KualitasMinyakAkses::mencatat($this->user());
    }

    protected function prepareForValidation(): void
    {
        $values = $this->all();
        array_walk_recursive($values, function (&$value, $key) {
            if (preg_match('/^(density|suhu|tinggi_air|volume)/', $key) && is_string($value)) {
                $value = str_replace(',', '.', $value);
            }
        });
        $this->replace($values);
    }

    public function rules(): array
    {
        $record = $this->route('kualitasMinyak');

        return [
            'dombak_id' => ['required', 'uuid', 'exists:dombak,dombak_id', ...($record ? [Rule::in([$record->dombak_id])] : [])],
            'tanggal' => ['required', 'date_format:Y-m-d', ...($record ? [Rule::in([$record->tanggal])] : []),
                Rule::unique('kualitas_minyak', 'tanggal')->where('dombak_id', $this->dombak_id)->ignore($record?->getKey(), 'kualitas_minyak_id')],
            'jam_awal' => ['required', 'date_format:H:i'],
            'density_awal' => ['required', 'numeric', 'between:0.690,0.899'],
            'suhu_awal' => ['required', 'numeric', 'between:0,50'],
            'tinggi_air' => ['required', 'numeric', 'between:0,999999.99'],
            'catatan' => ['nullable', 'string', 'max:4000'],
            'penerimaan' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return ['tanggal.unique' => 'Pemeriksaan Pagi sudah ada untuk tanggal dan dombak ini. Buka catatan harian untuk menambah Penerimaan atau Akhir.'];
    }
}
