<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class StatusPurchase extends Model
{
    use HasFactory;
    protected $table = 'status_purchase';
    protected $primaryKey = 'status_purchase_id';
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
        'deskripsi',
        'is_aktif'
    ];

    public function rencana_pembelian()
    {
        return $this->hasMany(RencanaPembelian::class, 'status_purchase_id', 'status_purchase_id');
    }
}
