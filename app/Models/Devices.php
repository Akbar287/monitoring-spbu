<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Devices extends Model
{
    use HasFactory;
    protected $table = 'devices';
    protected $primaryKey = 'devices_id';
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
        'ipv4',
        'type_devices',
        'model_devices',
        'os',
        'versi_os',
        'browser',
        'versi_browser'
    ];

    public function penjadwalan()
    {
        return $this->belongsTo(Penjadwalan::class, 'penjadwalan_id', 'penjadwalan_id');
    }
}
