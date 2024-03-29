<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class FilePembayaran extends Model
{
    use HasFactory;
    protected $table = 'file_pembayaran';
    protected $primaryKey = 'file_pembayaran_id';
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
        'pembayaran_id',
        'nama_file',
        'nama_dokumen',
        'tanggal_upload',
    ];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id', 'pembayaran_id');
    }
}
