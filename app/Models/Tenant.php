<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    public const STATUS_OPERASIONAL_BUKA = 'buka';
    public const STATUS_OPERASIONAL_TUTUP = 'tutup';

    protected $table = 'tenants';

    protected $primaryKey = 'id_tenant';

    protected $fillable = [
        'id_user',
        'nama_tenant',
        'nomor_kontak',
        'deskripsi',
        'foto',
        'status_operasional',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke akun User tenant.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Relasi ke daftar menu milik tenant.
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Relasi ke daftar sub-order yang masuk ke tenant.
     */
    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Memeriksa apakah tenant sedang operasional buka.
     */
    public function isBuka(): bool
    {
        return $this->status_operasional === self::STATUS_OPERASIONAL_BUKA && $this->is_active;
    }
}

