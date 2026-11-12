<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    use HasFactory;

    public const METODE_QRIS = 'qris';
    public const METODE_TUNAI = 'tunai';

    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const STATUS_SEBAGIAN_DIBAYAR = 'sebagian_dibayar';
    public const STATUS_SUDAH_DIBAYAR = 'sudah_dibayar';
    public const STATUS_DIBATALKAN = 'dibatalkan';
    public const STATUS_KEDALUWARSA = 'kedaluwarsa';

    protected $table = 'orders';

    protected $primaryKey = 'id_order';

    protected $fillable = [
        'kode_order',
        'id_meja',
        'nama_pembeli',
        'nomor_hp_pembeli',
        'metode_pembayaran',
        'total_harga',
        'status_pembayaran',
        'waktu_pesan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'total_harga' => 'decimal:2',
            'waktu_pesan' => 'datetime',
        ];
    }

    /**
     * Relasi ke meja fisik tempat pemesanan dilakukan.
     */
    public function meja(): BelongsTo
    {
        return $this->belongsTo(Meja::class, 'id_meja', 'id_meja');
    }

    /**
     * Relasi ke sub-order per tenant.
     */
    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class, 'id_order', 'id_order');
    }

    /**
     * Relasi ke catatan pembayaran untuk pesanan ini.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'id_order', 'id_order');
    }

    /**
     * Relasi ke seluruh detail item pesanan melalui sub-orders.
     */
    public function orderDetails(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderDetail::class,
            SubOrder::class,
            'id_order',     // Foreign key pada sub_orders table
            'id_suborder',  // Foreign key pada order_details table
            'id_order',     // Local key pada orders table
            'id_suborder'   // Local key pada sub_orders table
        );
    }
}

