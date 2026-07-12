<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['mitra_id', 'jalur_id', 'nama_basecamp', 'longitude', 'latitude', 'jam_operasional'])]
class Basecamp extends Model
{
    /**
     * Get the partner (owner) of the basecamp.
     *
     * @return BelongsTo<Mitra, Basecamp>
     */
    public function mitra(): BelongsTo
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }

    /**
     * Get the climbing trail associated with the basecamp.
     *
     * @return BelongsTo<JalurPendakian, Basecamp>
     */
    public function jalur(): BelongsTo
    {
        return $this->belongsTo(JalurPendakian::class, 'jalur_id');
    }

    /**
     * Get the products sold by this basecamp.
     *
     * @return HasMany<Produk, Basecamp>
     */
    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'basecamp_id');
    }

    /**
     * Get the orders placed at this basecamp.
     *
     * @return HasMany<Pesanan>
     */
    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'basecamp_id');
    }
}
