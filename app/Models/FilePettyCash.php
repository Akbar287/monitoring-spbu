<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class FilePettyCash extends Model
{
    use HasFactory;
    protected $table = 'file_petty_cash';
    protected $primaryKey = 'file_petty_cash_id';
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
        'petty_cash_id',
        'nama_file',
        'nama_dokumen',
        'tanggal_upload',
    ];

    public function petty_cash()
    {
        return $this->belongsTo(PettyCash::class, 'petty_cash_id', 'petty_cash_id');
    }
}
