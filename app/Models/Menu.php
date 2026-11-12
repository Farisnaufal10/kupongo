<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';

    protected $primaryKey = 'id_menu';

    protected $fillable = [
        'id_tenant',
        'nama_menu',
        'kategori',
        'deskripsi',
        'harga',
        'foto',
        'is_tersedia',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'is_tersedia' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke tenant pemilik menu.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Relasi ke riwayat detail pesanan yang memesan menu ini.
     */
    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'id_menu', 'id_menu');
    }
}

