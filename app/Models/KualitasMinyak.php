<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KualitasMinyak extends Model
{
    use HasUuids;

    protected $table = 'kualitas_minyak';

    protected $primaryKey = 'kualitas_minyak_id';

    protected $guarded = ['kualitas_minyak_id'];

    protected $casts = ['disetujui_pada' => 'datetime'];

    public function penerimaan()
    {
        return $this->hasMany(KualitasMinyakPenerimaan::class, 'kualitas_minyak_id')->orderBy('waktu_penerimaan');
    }
}
