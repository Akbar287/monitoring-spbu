<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Dispenser extends Model
{
    use HasFactory;
    protected $table = 'dispenser';
    protected $primaryKey = 'dispenser_id';
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
        'payung_id',
        'nama_dispenser',
        'keterangan',
        'is_aktif'
    ];

    public function payung()
    {
        return $this->belongsTo(Payung::class, 'payung_id', 'payung_id');
    }

    public function nozzle()
    {
        return $this->hasMany(Nozzle::class, 'dispenser_id', 'dispenser_id');
    }
}
