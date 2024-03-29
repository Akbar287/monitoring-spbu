<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class FilePost extends Model
{
    use HasFactory;
    protected $table = 'file_post';
    protected $primaryKey = 'file_post_id';
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
        'post_id',
        'nama_file',
        'nama_dokumen',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }
}
