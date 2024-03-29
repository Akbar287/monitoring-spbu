<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PajakPembelianLib extends Model
{
    use HasFactory;
    protected $table = 'pajak_pembelian_lib';
    protected $primaryKey = 'pajak_pembelian_lib_id';
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
        'ppn',
        'ppbkb',
        'pph',
        'is_aktif'
    ];

    public function pajak_pembelian()
    {
        return $this->hasMany(PajakPembelian::class, 'pajak_pembelian_lib_id', 'pajak_pembelian_lib_id');
    }
}
