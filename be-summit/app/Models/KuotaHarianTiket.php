<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'produk_tiket_id',
    'tanggal',
    'kuota_total',
    'kuota_tersisa',
])]
class KuotaHarianTiket extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kuota_harian_tikets';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'kuota_total' => 'integer',
            'kuota_tersisa' => 'integer',
        ];
    }

    /**
     * Get the specific ticket template for this quota.
     *
     * @return BelongsTo<ProdukTiket, KuotaHarianTiket>
     */
    public function tiket(): BelongsTo
    {
        return $this->belongsTo(ProdukTiket::class, 'produk_tiket_id');
    }
}
