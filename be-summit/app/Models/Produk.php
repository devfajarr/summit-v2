<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'basecamp_id',
    'nama_produk',
    'kategori',
    'deskripsi',
    'harga',
    'stok',
    'satuan',
    'is_active',
    'gambar',
])]
class Produk extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'harga' => 'decimal:2',
            'stok' => 'integer',
        ];
    }

    /**
     * Get the basecamp that sells this product.
     *
     * @return BelongsTo<Basecamp, Produk>
     */
    public function basecamp(): BelongsTo
    {
        return $this->belongsTo(Basecamp::class, 'basecamp_id');
    }

    /**
     * Get the open trip details associated with this product.
     *
     * @return HasOne<ProdukOpentrip>
     */
    public function opentrip(): HasOne
    {
        return $this->hasOne(ProdukOpentrip::class, 'produk_id');
    }

    /**
     * Get the ticket details associated with this product.
     *
     * @return HasOne<ProdukTiket>
     */
    public function tiket(): HasOne
    {
        return $this->hasOne(ProdukTiket::class, 'produk_id');
    }

    /**
     * Get the order line items referencing this product.
     *
     * @return HasMany<DetailPesanan>
     */
    public function detailPesanans(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'produk_id');
    }
}
