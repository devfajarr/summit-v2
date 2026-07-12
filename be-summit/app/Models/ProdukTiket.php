<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'produk_id',
    'jalur_id',
    'jam_buka',
    'jam_tutup',
])]
class ProdukTiket extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'produk_tikets';

    /**
     * Get the base product profile.
     *
     * @return BelongsTo<Produk, ProdukTiket>
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /**
     * Get the climbing trail associated with the ticket.
     *
     * @return BelongsTo<JalurPendakian, ProdukTiket>
     */
    public function jalur(): BelongsTo
    {
        return $this->belongsTo(JalurPendakian::class, 'jalur_id');
    }

    /**
     * Get the daily quotas for this ticket.
     *
     * @return HasMany<KuotaHarianTiket>
     */
    public function kuotas(): HasMany
    {
        return $this->hasMany(KuotaHarianTiket::class, 'produk_tiket_id');
    }
}
