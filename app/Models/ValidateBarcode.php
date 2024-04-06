<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class ValidateBarcode extends Model
{
    use HasFactory;
    protected $table = 'validate_barcode';
    protected $primaryKey = 'validate_barcode_id';
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
        'is_expired',
        'is_active'
    ];

    public function random_string_barcode()
    {
        return $this->hasMany(RandomStringBarcode::class, 'validate_barcode_id', 'validate_barcode_id');
    }
}
