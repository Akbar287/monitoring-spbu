<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Hari extends Model
{
    use HasFactory;
    protected $table = 'hari';
    protected $primaryKey = 'hari_id';
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
        'nama_hari',
        'hari_kerja',
        'keterangan',
    ];

    public function jam_kerja()
    {
        return $this->belongsToMany(JamKerja::class, 'hari_jam_kerja', 'hari_id', 'jam_kerja_id');
    }
}
