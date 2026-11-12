<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Primary key table users sesuai rancangan ERD KuponGo.
     *
     * @var string
     */
    protected $primaryKey = 'id_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi one-to-one ke profil Pengelola.
     */
    public function pengelola(): HasOne
    {
        return $this->hasOne(Pengelola::class, 'id_user', 'id_user');
    }

    /**
     * Relasi one-to-one ke profil Tenant.
     */
    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class, 'id_user', 'id_user');
    }

    /**
     * Memeriksa apakah user memiliki role pengelola.
     */
    public function isPengelola(): bool
    {
        return $this->role === 'pengelola';
    }

    /**
     * Memeriksa apakah user memiliki role tenant.
     */
    public function isTenant(): bool
    {
        return $this->role === 'tenant';
    }
}
