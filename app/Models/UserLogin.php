<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Ramsey\Uuid\Uuid;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;

class UserLogin extends Authenticatable
{
    // HasApiTokens dipakai API mobile (routes/mobile.php). Kolom
    // personal_access_tokens.tokenable_id sudah diubah ke varchar(36) lewat
    // migrasi 2026_08_13_000001 agar muat UUID.
    use HasApiTokens, HasFactory, softDeletes;
    protected $table = 'userlogin';
    protected $primaryKey = 'user_login_id';
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
        'username',
        'password',
        'is_aktif',
        'access_token',
        'access',
        'last_login'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }
}
