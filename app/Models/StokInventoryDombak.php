<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class StokInventoryDombak extends Model
{
    use HasFactory;
    protected $table = 'stok_inventory_dombak';
    protected $primaryKey = 'stok_inventory_dombak_id';
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
        'dombak_id',
        'stok_inventory_id',
        'stok'
    ];

    public function stok_inventory()
    {
        return $this->belongsTo(StokInventory::class, 'stok_inventory_id', 'stok_inventory_id');
    }

    public function dombak()
    {
        return $this->belongsTo(Dombak::class, 'dombak_id', 'dombak_id');
    }
}
