<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class StatusSetoran extends Model
{
    use HasFactory;
    protected $table = 'status_setoran';
    protected $primaryKey = 'status_setoran_id';
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
        'nama_status'
    ];

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'status_setoran_id', 'status_setoran_id');
    }
}
