<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DetailRencanaPembelianMs2 extends Model
{
    use HasFactory;
    protected $table = 'detail_rencana_pembelian_ms2';
    protected $primaryKey = 'detail_rencana_pembelian_ms2_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'ms2_id',
        'jam_kerja_id',
        'detail_rencana_pembelian_id',
        'is_delivery'
    ];

    public function ms2()
    {
        return $this->belongsTo(Ms2::class, 'ms2_id', 'ms2_id');
    }
    public function jam_kerja()
    {
        return $this->belongsTo(JamKerja::class, 'jam_kerja_id', 'jam_kerja_id');
    }
    public function detail_rencana_pembelian()
    {
        return $this->belongsTo(DetailRencanaPembelian::class, 'detail_rencana_pembelian_id', 'detail_rencana_pembelian_id');
    }
}
