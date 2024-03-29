<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Losses extends Model
{
    use HasFactory;
    protected $table = 'losses';
    protected $primaryKey = 'losses_id';
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
        'jenis_losses_id',
        'dokumen_stok_id',
        'tanggal',
        'simbol',
        'jumlah',
    ];

    public function jenis_losses()
    {
        return $this->belongsTo(JenisLosses::class, 'jenis_losses_id', 'jenis_losses_id');
    }

    public function dokumen_stok()
    {
        return $this->belongsTo(DokumenStok::class, 'dokumen_stok_id', 'dokumen_stok_id');
    }
}
