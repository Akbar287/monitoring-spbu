<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class SatuanUkurTinggi extends Model
{
    use HasFactory;
    protected $table = 'satuan_ukur_tinggi';
    protected $primaryKey = 'satuan_ukur_tinggi_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }
    public $timestamps = false;
    protected $fillable = [
        'nama_satuan',
        'singkatan'
    ];

    public function konversi()
    {
        return $this->hasMany(Konversi::class, 'satuan_ukur_tinggi_id', 'satuan_ukur_tinggi_id');
    }
}
