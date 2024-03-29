<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class AreaMember extends Model
{
    use HasFactory;
    protected $table = 'area_member';
    protected $primaryKey = 'area_member_id';
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
        'member_id',
        'provinsi',
        'kabupaten',
        'kecamatan',
        'desa',
        'rw',
        'rt',
        'no',
        'alamat',
        'is_primary',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }
}
