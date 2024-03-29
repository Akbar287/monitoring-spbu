<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

use Illuminate\Database\Eloquent\SoftDeletes;

class Ktp extends Model
{
    use HasFactory, softDeletes;
    protected $table = 'ktp';
    protected $primaryKey = 'ktp_id';
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
        'nik',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'verified'
    ];

    public function member()
    {
        return $this->hasOne(Member::class, 'ktp_id', 'ktp_id');
    }
}
