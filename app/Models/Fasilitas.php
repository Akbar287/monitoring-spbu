<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Fasilitas extends Model
{
    use HasFactory;
    protected $table = 'fasilitas';
    protected $primaryKey = 'fasilitas_id';
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
        'spbu_id',
        'nama_fasilitas',
        'jumlah',
        'tanggal_digunakan',
        'keterangan',
    ];

    public function spbu()
    {
        return $this->belongsTo(Spbu::class, 'spbu_id', 'spbu_id');
    }
    public function file_fasilitas()
    {
        return $this->hasMany(FileFasilitas::class, 'fasilitas_id', 'fasilitas_id');
    }
}
