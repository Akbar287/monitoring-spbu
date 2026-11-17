<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KualitasMinyakPenerimaan extends Model
{
    protected $table = 'kualitas_minyak_penerimaan';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'truk_berwenang' => 'boolean',
        'penerimaan_disetujui_pada' => 'datetime',
        'akhir_disetujui_pada' => 'datetime',
    ];

    public function peringatan(): array
    {
        $pesan = [];
        if (! $this->truk_berwenang) {
            $pesan[] = 'Truk tidak berwenang.';
        }
        if (abs((float) $this->selisih_density) > 0.00500001) {
            $pesan[] = 'Selisih density penerimaan melebihi 0,005 g/cm³.';
        }
        if ($this->volume_pnbp && $this->volume_diterima !== null
            && abs((float) $this->volume_diterima - (float) $this->volume_pnbp) / (float) $this->volume_pnbp * 100 > 0.15000001) {
            $pesan[] = 'Selisih volume penerimaan melebihi 0,15%.';
        }

        return $pesan;
    }
}
