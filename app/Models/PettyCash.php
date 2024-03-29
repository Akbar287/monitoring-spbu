<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class PettyCash extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'petty_cash';
    protected $primaryKey = 'petty_cash_id';
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
        'no_kode',
        'no_bukti',
        'keterangan',
        'jumlah',
        'jenis',
        'tanggal',
    ];

    public function spbu()
    {
        return $this->belongsTo(Spbu::class, 'spbu_id', 'spbu_id');
    }
    public function file_petty_cash()
    {
        return $this->hasMany(FilePettyCash::class, 'petty_cash_id', 'petty_cash_id');
    }
    public function petty_cash()
    {
        return $this->belongsToMany(PettyCash::class, 'tera_petty_cash', 'petty_cash_id', 'tera_id');
    }

    public function penerimaan()
    {
        return $this->belongsToMany(Penerimaan::class, 'penerimaan_petty_cash', 'petty_cash_id', 'penerimaan_id');
    }
}
