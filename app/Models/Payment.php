<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const METODE_QRIS = 'qris';
    public const METODE_TUNAI = 'tunai';

    public const STATUS_PENDING = 'pending';
    public const STATUS_BERHASIL = 'berhasil';
    public const STATUS_GAGAL = 'gagal';
    public const STATUS_KEDALUWARSA = 'kedaluwarsa';

    protected $table = 'payments';

    protected $primaryKey = 'id_payment';

    protected $fillable = [
        'id_order',
        'id_suborder',
        'kode_payment',
        'idempotency_key',
        'transaction_id',
        'metode_pembayaran',
        'jumlah_bayar',
        'status_pembayaran',
        'waktu_pembayaran',
        'payload_transaksi',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_bayar' => 'decimal:2',
            'waktu_pembayaran' => 'datetime',
            'payload_transaksi' => 'array',
        ];
    }

    /**
     * The "booted" method of the model.
     * Memastikan id_suborder selalu terkait dengan id_order yang sama.
     */
    protected static function booted(): void
    {
        static::saving(function (Payment $payment) {
            if ($payment->id_suborder !== null) {
                $subOrder = SubOrder::find($payment->id_suborder);
                if ($subOrder && (int) $subOrder->id_order !== (int) $payment->id_order) {
                    throw new \InvalidArgumentException("Integritas pembayaran gagal: sub_order ({$payment->id_suborder}) bukan milik master order ({$payment->id_order}).");
                }
            }
        });
    }

    /**
     * Relasi ke master order utama.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }

    /**
     * Relasi ke sub-order tertentu (untuk pembayaran tunai per tenant; bernilai null pada QRIS multi-tenant).
     */
    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class, 'id_suborder', 'id_suborder');
    }

    /**
     * Memeriksa apakah pembayaran ini berlaku untuk keseluruhan master order.
     */
    public function isMasterOrderPayment(): bool
    {
        return is_null($this->id_suborder);
    }

    /**
     * Memeriksa apakah pembayaran ini merupakan pembayaran parsial sub-order.
     */
    public function isSubOrderPayment(): bool
    {
        return !is_null($this->id_suborder);
    }
}
