<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, softDeletes;
    protected $table = 'member';
    protected $primaryKey = 'member_id';
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
        'ktp_id',
        'status_member_id',
        'email',
        'no_hp',
        'no_wa',
        'avatar',
        'bergabung_sejak'
    ];

    public function status_member()
    {
        return $this->belongsTo(StatusMember::class, 'status_member_id', 'status_member_id');
    }
    public function ktp()
    {
        return $this->belongsTo(Ktp::class, 'ktp_id', 'ktp_id');
    }
    public function spbu()
    {
        return $this->belongsToMany(Spbu::class, 'spbu_member', 'member_id', 'spbu_id');
    }
    public function user_login()
    {
        return $this->hasOne(UserLogin::class, 'member_id', 'member_id');
    }
    public function post()
    {
        return $this->hasMany(Post::class, 'member_id', 'member_id');
    }
    public function activity_log()
    {
        return $this->hasMany(ActivityLog::class, 'member_id', 'member_id');
    }
    public function area_member()
    {
        return $this->hasMany(AreaMember::class, 'member_id', 'member_id');
    }
    public function bonus()
    {
        return $this->hasMany(Bonus::class, 'member_id', 'member_id');
    }
    public function jabatan()
    {
        return $this->belongsToMany(Jabatan::class, 'jabatan_member', 'member_id', 'jabatan_id');
    }
    public function gaji_member()
    {
        return $this->hasMany(GajiMember::class, 'member_id', 'member_id');
    }

    public function dokumen_stok()
    {
        return $this->hasMany(DokumenStok::class, 'member_id', 'member_id');
    }

    public function rencana_pembelian()
    {
        return $this->hasMany(RencanaPembelian::class, 'member_id', 'member_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'member_id', 'member_id');
    }

    public function penarikan()
    {
        return $this->hasMany(Penarikan::class, 'member_id', 'member_id');
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class, 'member_id', 'member_id');
    }

    public function penjadwalan()
    {
        return $this->hasMany(Penjadwalan::class, 'member_id', 'member_id');
    }
    public function pj_operasional_setoran()
    {
        return $this->hasMany(PjOperasionalSetoran::class, 'member_id', 'member_id');
    }
}
