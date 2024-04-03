<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Penjadwalan extends Model
{
    use HasFactory;
    protected $table = 'penjadwalan';
    protected $primaryKey = 'penjadwalan_id';
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
        'status_kehadiran_id',
        'jabatan_id',
        'member_id',
        'jam_kerja_id',
        'tanggal',
        'deskripsi',
        'is_attended',
        'is_confirm',
        'attended'
    ];

    public function status_kehadiran()
    {
        return $this->belongsTo(StatusKehadiran::class, 'status_kehadiran_id', 'status_kehadiran_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'jabatan_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function jam_kerja()
    {
        return $this->belongsTo(JamKerja::class, 'jam_kerja_id', 'jam_kerja_id');
    }
}
