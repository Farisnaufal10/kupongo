<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meja extends Model
{
    use HasFactory;

    public const STATUS_TERSEDIA = 'tersedia';
    public const STATUS_TERISI = 'terisi';
    public const STATUS_TIDAK_AKTIF = 'tidak_aktif';

    protected $table = 'meja';

    protected $primaryKey = 'id_meja';

    protected $fillable = [
        'nomor_meja',
        'qr_code_path',
        'kapasitas',
        'status',
    ];

    /**
     * Relasi ke pesanan (orders) yang dilakukan dari meja ini.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'id_meja', 'id_meja');
    }
}
