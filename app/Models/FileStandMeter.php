<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class FileStandMeter extends Model
{
    use HasFactory;
    protected $table = 'file_stand_meter';
    protected $primaryKey = 'file_stand_meter_id';
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
        'stand_meter_id',
        'nama_file',
        'nama_dokumen',
        'tanggal_upload',
    ];

    public function stand_meter()
    {
        return $this->belongsTo(StandMeter::class, 'stand_meter_id', 'stand_meter_id');
    }
}
