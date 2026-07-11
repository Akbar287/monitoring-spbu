<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Jurnal extends Model
{
    use HasFactory;
    protected $table = 'jurnal';
    protected $primaryKey = 'jurnal_id';
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
        'nomor_jurnal',
        'tanggal',
        'keterangan',
        'tipe_sumber',
        'sumber_id',
        'is_posted',
        'posted_by',
    ];

    public function spbu()
    {
        return $this->belongsTo(Spbu::class, 'spbu_id', 'spbu_id');
    }

    public function jurnal_detail()
    {
        return $this->hasMany(JurnalDetail::class, 'jurnal_id', 'jurnal_id')->orderBy('urutan');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'posted_by', 'member_id');
    }
}
