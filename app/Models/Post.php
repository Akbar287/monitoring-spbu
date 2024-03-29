<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Post extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'post';
    protected $primaryKey = 'post_id';
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
        'title',
        'content',
        'active'
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function tag()
    {
        return $this->belongsToMany(Tag::class, 'tag_post', 'post_id', 'tag_id');
    }

    public function kategori()
    {
        return $this->belongsToMany(Kategori::class, 'kategori_post', 'post_id', 'kategori_id');
    }

    public function file_post()
    {
        return $this->hasMany(FilePost::class, 'post_id', 'post_id');
    }
}
