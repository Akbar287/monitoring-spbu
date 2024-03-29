<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Tag extends Model
{
    use HasFactory;
    protected $table = 'tag';
    protected $primaryKey = 'tag_id';
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
        'nama_tag'
    ];

    public function post()
    {
        return $this->belongsToMany(Post::class, 'tag_post', 'tag_id', 'post_id');
    }
}
