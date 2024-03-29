<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Payung extends Model
{
    use HasFactory;
    protected $table = 'payung';
    protected $primaryKey = 'payung_id';
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
        'spbu_id',
        'nama_payung',
        'keterangan',
        'is_aktif',
    ];

    public function dombak()
    {
        return $this->belongsToMany(Dombak::class, 'dombak_payung', 'payung_id', 'dombak_id');
    }

    public function spbu()
    {
        return $this->belongsTo(Spbu::class, 'spbu_id', 'spbu_id');
    }

    public function dispenser()
    {
        return $this->hasMany(Dispenser::class, 'payung_id', 'payung_id');
    }
}
