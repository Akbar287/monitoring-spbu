<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

use Illuminate\Database\Eloquent\SoftDeletes;
class Divisi extends Model
{
    use HasFactory, softDeletes;
    protected $table = 'divisi';
    protected $primaryKey = 'divisi_id';
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
        'nama_divisi',
        'keterangan',
        'is_aktif',
    ];

    public function spbu()
    {
        return $this->belongsTo(Spbu::class, 'spbu_id', 'spbu_id');
    }
    public function level()
    {
        return $this->hasMany(Level::class, 'divisi_id', 'divisi_id');
    }
}
