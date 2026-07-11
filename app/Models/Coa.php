<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Coa extends Model
{
    use HasFactory;
    protected $table = 'coa';
    protected $primaryKey = 'coa_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = $model->{$model->getKeyName()} ?? Uuid::uuid4()->toString();
        });
    }
    protected $fillable = [
        'parent_coa_id',
        'kode_akun',
        'nama_akun',
        'tipe_akun',
        'saldo_normal',
        'kategori_arus_kas',
        'is_kas',
        'is_header',
        'is_aktif',
        'keterangan',
    ];

    public const TIPE_AKUN = ['aset', 'kewajiban', 'modal', 'pendapatan', 'hpp', 'beban', 'pendapatan_lain', 'beban_lain'];

    // tipe akun nominal (masuk laba rugi); sisanya akun riil (masuk neraca)
    public const TIPE_NOMINAL = ['pendapatan', 'hpp', 'beban', 'pendapatan_lain', 'beban_lain'];

    public function parent()
    {
        return $this->belongsTo(Coa::class, 'parent_coa_id', 'coa_id');
    }

    public function children()
    {
        return $this->hasMany(Coa::class, 'parent_coa_id', 'coa_id');
    }

    public function jurnal_detail()
    {
        return $this->hasMany(JurnalDetail::class, 'coa_id', 'coa_id');
    }

    public function isNominal(): bool
    {
        return in_array($this->tipe_akun, self::TIPE_NOMINAL);
    }
}
