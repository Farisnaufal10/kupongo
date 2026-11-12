<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengelola extends Model
{
    use HasFactory;

    protected $table = 'pengelola';

    protected $primaryKey = 'id_pengelola';

    protected $fillable = [
        'id_user',
        'nama',
        'no_hp',
    ];

    /**
     * Relasi ke akun User pengelola.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}

