<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Kategori extends Model
{
    use HasFactory;
    protected $table = 'kategori';
    protected $primaryKey = 'kategori_id';
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
        'nama_kategori',
        'detail'
    ];

    public function post()
    {
        return $this->belongsToMany(Post::class, 'kategori_post', 'kategori_id', 'post_id');
    }
}
