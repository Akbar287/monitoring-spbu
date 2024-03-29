<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DetailGaji extends Model
{
    use HasFactory;
    protected $table = 'detail_gaji';
    protected $primaryKey = 'detail_gaji_id';
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
        'gaji_member_id',
        'nama_gaji',
        'type',
        'total'
    ];

    public function gaji_member()
    {
        return $this->belongsTo(GajiMember::class, 'gaji_member_id', 'gaji_member_id');
    }
}
