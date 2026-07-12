<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'gunung_id',
    'nama_jalur',
    'deskripsi',
    'titik_awal_mdpl',
    'titik_akhir_mdpl',
    'waktu_tempuh',
    'status',
    'panjang_jalur',
    'tingkat_kesulitan',
])]
class JalurPendakian extends Model
{
    /**
     * Get the mountain that owns the trail.
     *
     * @return BelongsTo<Gunung, JalurPendakian>
     */
    public function gunung(): BelongsTo
    {
        return $this->belongsTo(Gunung::class, 'gunung_id');
    }

    /**
     * Get the ticket templates associated with this trail.
     *
     * @return HasMany<ProdukTiket, JalurPendakian>
     */
    public function produkTikets(): HasMany
    {
        return $this->hasMany(ProdukTiket::class, 'jalur_id');
    }

    /**
     * Get the orders booked for this trail.
     *
     * @return HasMany<Pesanan>
     */
    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'jalur_id');
    }
}
