<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';

    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'id_suborder',
        'id_menu',
        'nama_menu',
        'harga_satuan',
        'jumlah',
        'subtotal',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'jumlah' => 'integer',
        ];
    }

    /**
     * Relasi ke sub-order induk.
     */
    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class, 'id_suborder', 'id_suborder');
    }

    /**
     * Relasi ke menu yang dipesan (dapat bernilai null jika menu dihapus).
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'id_menu', 'id_menu');
    }
}
