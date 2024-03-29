<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

use Illuminate\Database\Eloquent\SoftDeletes;

class Level extends Model
{
    use HasFactory, softDeletes;
    protected $table = 'level';
    protected $primaryKey = 'level_id';
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
        'divisi_id',
        'nama_level',
        'keterangan',
        'is_aktif',
    ];

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id', 'divisi_id');
    }
    public function jabatan()
    {
        return $this->hasMany(Jabatan::class, 'level_id', 'level_id');
    }
}
