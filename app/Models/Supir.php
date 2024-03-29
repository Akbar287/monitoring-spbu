<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Supir extends Model
{
    use HasFactory;
    protected $table = 'supir';
    protected $primaryKey = 'supir_id';
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
        'pengiriman_id',
        'nama_supir'
    ];

    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id', 'pengiriman_id');
    }
}
