<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Pengiriman extends Model
{
    use HasFactory;
    protected $table = 'pengiriman';
    protected $primaryKey = 'pengiriman_id';
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
        'member_id',
        'tanggal',
        'no_do',
        'no_pol',
        'is_confirmation_admin',
        'is_confirmation_top',
        'is_done',
        'catatan',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function supir()
    {
        return $this->hasMany(Supir::class, 'pengiriman_id', 'pengiriman_id');
    }

    public function file_lo()
    {
        return $this->hasMany(FileLo::class, 'pengiriman_id', 'pengiriman_id');
    }

    public function ms2()
    {
        return $this->belongsToMany(Ms2::class, 'pengiriman_ms2', 'pengiriman_id', 'ms2_id');
    }
}
