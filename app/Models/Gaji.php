<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Gaji extends Model
{
    use HasFactory;
    protected $table = 'gaji';
    protected $primaryKey = 'gaji_id';
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
        'jabatan_id',
        'tanggal_berlaku',
        'total_gaji',
        'keterangan',
        'is_aktif'
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'jabatan_id');
    }
    public function gaji_member()
    {
        return $this->hasMany(GajiMember::class, 'gaji_id', 'gaji_id');
    }
}
