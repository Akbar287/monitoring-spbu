<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Aset extends Model
{
    use HasFactory;
    protected $table = 'aset';
    protected $primaryKey = 'aset_id';
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
        'nama_aset',
        'jumlah',
        'tanggal_digunakan',
        'harga',
        'penyusutan_per_hari',
        'is_digunakan',
        'keterangan',
    ];

    public function spbu()
    {
        return $this->belongsTo(Spbu::class, 'spbu_id', 'spbu_id');
    }
    public function file_aset()
    {
        return $this->hasMany(FileAset::class, 'aset_id', 'aset_id');
    }
}
