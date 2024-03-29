<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class JenisLosses extends Model
{
    use HasFactory;
    protected $table = 'jenis_losses';
    protected $primaryKey = 'jenis_losses_id';
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
        'jenis_loss',
        'deskripsi'
    ];

    public function losses()
    {
        return $this->hasMany(Losses::class, 'jenis_losses_id', 'jenis_losses_id');
    }
}
