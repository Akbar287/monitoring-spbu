<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Ms2 extends Model
{
    use HasFactory;
    protected $table = 'ms2';
    protected $primaryKey = 'ms2_id';
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
        'member_id',
        'tanggal',
        'kode_sms',
        'is_done'
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function detail_rencana_pembelian_ms2()
    {
        return $this->hasMany(DetailRencanaPembelianMs2::class, 'ms2_id', 'ms2_id');
    }

    public function pengiriman()
    {
        return $this->belongsToMany(Pengiriman::class, 'pengiriman_ms2', 'ms2_id', 'pengiriman_id');
    }
}
