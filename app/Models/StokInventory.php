<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class StokInventory extends Model
{
    use HasFactory;
    protected $table = 'stok_inventory';
    protected $primaryKey = 'stok_inventory_id';
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
        'produk_id',
        'stok',
    ];

    public function stok_inventory_dombak()
    {
        return $this->hasMany(StokInventoryDombak::class, 'stok_inventory_id', 'stok_inventory_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }

    public function dokumen_stok()
    {
        return $this->hasMany(DokumenStok::class, 'stok_inventory_id', 'stok_inventory_id');
    }
}
