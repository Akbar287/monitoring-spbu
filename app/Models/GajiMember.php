<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class GajiMember extends Model
{
    use HasFactory;
    protected $table = 'gaji_member';
    protected $primaryKey = 'gaji_member_id';
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
        'gaji_id',
        'member_id',
        'tanggal',
        'total_gaji_bersih',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function gaji()
    {
        return $this->belongsTo(Gaji::class, 'gaji_id', 'gaji_id');
    }

    public function detail_gaji()
    {
        return $this->hasMany(DetailGaji::class, 'gaji_member_id', 'gaji_member_id');
    }
}
