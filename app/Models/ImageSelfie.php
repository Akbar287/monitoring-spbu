<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class ImageSelfie extends Model
{
    use HasFactory;
    protected $table = 'image_selfie';
    protected $primaryKey = 'image_selfie_id';
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
        'penjadwalan_id',
        'nama_file'
    ];

    public function penjadwalan()
    {
        return $this->belongsTo(Penjadwalan::class, 'penjadwalan_id', 'penjadwalan_id');
    }
}
