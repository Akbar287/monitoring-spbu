<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Segel extends Model
{
    use HasFactory;
    protected $table = 'segel';
    protected $primaryKey = 'segel_id';
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
        'file_lo_id',
        'no_segel'
    ];

    public function file_lo()
    {
        return $this->belongsTo(FileLo::class, 'file_lo_id', 'file_lo_id');
    }
}
