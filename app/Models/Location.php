<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Location extends Model
{
    use HasFactory;
    protected $table = 'location';
    protected $primaryKey = 'location_id';
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
        'penjadwalan_id',
        'lattitude',
        'longitude',
        'gmt',
        'deskripsi'
    ];

    public function penjadwalan()
    {
        return $this->belongsTo(Penjadwalan::class, 'penjadwalan_id', 'penjadwalan_id');
    }
}
