<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

use Illuminate\Database\Eloquent\SoftDeletes;

class Jabatan extends Model
{
    use HasFactory, softDeletes;
    protected $table = 'jabatan';
    protected $primaryKey = 'jabatan_id';
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
        'level_id',
        'nama_jabatan',
        'keterangan',
        'is_aktif'
    ];

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'level_id');
    }
    public function gaji()
    {
        return $this->hasMany(Gaji::class, 'jabatan_id', 'jabatan_id');
    }
    public function penjadwalan()
    {
        return $this->hasMany(Penjadwalan::class, 'jabatan_id', 'jabatan_id');
    }
    public function member()
    {
        return $this->belongsToMany(Member::class, 'jabatan_member', 'jabatan_id', 'member_id');
    }
}
