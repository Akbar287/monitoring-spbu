<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Tera extends Model
{
    use HasFactory;
    protected $table = 'tera';
    protected $primaryKey = 'tera_id';
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
        'spbu_id',
        'tanggal',
        'grand_total',
        'keterangan',
    ];

    public function detail_tera()
    {
        return $this->hasMany(DetailTera::class, 'tera_id', 'tera_id');
    }
    public function spbu()
    {
        return $this->belongsTo(Spbu::class, 'spbu_id', 'spbu_id');
    }
    public function petty_cash()
    {
        return $this->belongsToMany(PettyCash::class, 'tera_petty_cash', 'tera_id', 'petty_cash_id');
    }
}
