<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class RandomStringBarcode extends Model
{
    use HasFactory;
    protected $table = 'random_string_barcode';
    protected $primaryKey = 'random_string_barcode_id';
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
        'barcode',
        'start',
        'finish',
        'is_done'
    ];

    public function penjadwalan()
    {
        return $this->belongsTo(Penjadwalan::class, 'penjadwalan_id', 'penjadwalan_id');
    }
}
