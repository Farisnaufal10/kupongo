<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubOrder extends Model
{
    use HasFactory;

    public const STATUS_PEMBAYARAN_MENUNGGU = 'menunggu_pembayaran';
    public const STATUS_PEMBAYARAN_SUDAH = 'sudah_dibayar';
    public const STATUS_PEMBAYARAN_DIBATALKAN = 'dibatalkan';

    public const STATUS_PESANAN_MENUNGGU = 'menunggu';
    public const STATUS_PESANAN_DIPROSES = 'diproses';
    public const STATUS_PESANAN_SIAP = 'siap_disajikan';
    public const STATUS_PESANAN_SELESAI = 'selesai';
    public const STATUS_PESANAN_DIBATALKAN = 'dibatalkan';

    protected $table = 'sub_orders';

    protected $primaryKey = 'id_suborder';

    protected $fillable = [
        'id_order',
        'id_tenant',
        'kode_suborder',
        'subtotal',
        'status_pembayaran',
        'status_pesanan',
        'catatan',
        'waktu_selesai',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'waktu_selesai' => 'datetime',
        ];
    }

    /**
     * Relasi ke master order utama.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }

    /**
     * Relasi ke tenant pemilik sub-order.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Relasi ke rincian item pesanan dalam sub-order ini.
     */
    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'id_suborder', 'id_suborder');
    }

    /**
     * Relasi ke catatan pembayaran parsial/tunai untuk sub-order ini.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'id_suborder', 'id_suborder');
    }
}

