<?php

namespace App\Http\Requests;

use App\Support\KualitasMinyakAkses;
use Illuminate\Foundation\Http\FormRequest;

class KualitasMinyakPemeriksaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $parent = $this->route('kualitasMinyak');
        $row = $this->route('penerimaan');
        abort_if($row && $row->kualitas_minyak_id !== $parent->getKey(), 404);
        $tahap = $this->routeIs('kualitas-minyak.akhir.simpan') ? 'akhir' : 'penerimaan';
        if ($row?->{$tahap.'_disetujui_pada'}) {
            return false;
        }
        $owner = $row?->{$tahap.'_dibuat_oleh'};

        return $owner
            ? $owner === $this->user()->getKey() || KualitasMinyakAkses::menyetujui($this->user())
            : KualitasMinyakAkses::mencatat($this->user());
    }

    protected function prepareForValidation(): void
    {
        $values = $this->all();
        foreach ($values as $key => &$value) {
            if (preg_match('/^(density|suhu|volume)/', $key) && is_string($value)) {
                $value = str_replace(',', '.', $value);
            }
        }
        $this->replace($values);
    }

    public function rules(): array
    {
        if ($this->routeIs('kualitas-minyak.akhir.simpan')) {
            return [
                'waktu_akhir' => ['required', 'date_format:Y-m-d\TH:i'],
                'density_akhir' => ['required', 'numeric', 'between:0.690,0.899'],
                'suhu_akhir' => ['required', 'numeric', 'between:0,50'],
                'catatan_akhir' => ['nullable', 'string', 'max:4000'],
            ];
        }

        return [
            'waktu_penerimaan' => ['required', 'date_format:Y-m-d\TH:i'],
            'nomor_mobil_tangki' => ['required', 'string', 'max:32'],
            'nomor_pnbp' => ['required', 'string', 'max:64'],
            'truk_berwenang' => ['required', 'boolean'],
            'density_15_pnbp' => ['required', 'numeric', 'between:0.690,0.950', 'decimal:0,4'],
            'density_penerimaan' => ['required', 'numeric', 'between:0.690,0.899'],
            'suhu_penerimaan' => ['required', 'numeric', 'between:0,50'],
            'volume_pnbp' => ['nullable', 'required_with:volume_diterima', 'numeric', 'gt:0', 'max:999999999', 'decimal:0,3'],
            'volume_diterima' => ['nullable', 'required_with:volume_pnbp', 'numeric', 'min:0', 'max:999999999', 'decimal:0,3'],
            'catatan_penerimaan' => ['nullable', 'string', 'max:4000'],
        ];
    }

}
