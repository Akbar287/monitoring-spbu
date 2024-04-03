<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class StatusKehadiran extends Model
{
    use HasFactory;
    protected $table = 'status_kehadiran';
    protected $primaryKey = 'status_kehadiran_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }
    protected $fillable = [
        'nama_kehadiran'
    ];

    public function penjadwalan()
    {
        return $this->hasMany(Penjadwalan::class, 'status_kehadiran_id', 'status_kehadiran_id');
    }
}
