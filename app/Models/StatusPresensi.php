<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class StatusPresensi extends Model
{
    use HasFactory;
    protected $table = 'status_presensi';
    protected $primaryKey = 'status_presensi_id';
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
        'nama_status',
        'keterangan'
    ];

    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'status_presensi_id', 'status_presensi_id');
    }
}
