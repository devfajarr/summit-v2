<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'invoice',
    'user_id',
    'basecamp_id',
    'jalur_id',
    'status',
    'subtotal',
    'tanggal_booking',
    'diskon',
    'biaya_layanan_user',
    'komisi_admin',
    'pendapatan_mitra',
    'total_bayar',
])]
class Pesanan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pesanans';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_booking' => 'date',
            'subtotal' => 'decimal:2',
            'diskon' => 'decimal:2',
            'biaya_layanan_user' => 'decimal:2',
            'komisi_admin' => 'decimal:2',
            'pendapatan_mitra' => 'decimal:2',
            'total_bayar' => 'decimal:2',
        ];
    }

    /**
     * Get the user who made the order.
     *
     * @return BelongsTo<User, Pesanan>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the basecamp where the order is hosted.
     *
     * @return BelongsTo<Basecamp, Pesanan>
     */
    public function basecamp(): BelongsTo
    {
        return $this->belongsTo(Basecamp::class, 'basecamp_id');
    }

    /**
     * Get the climbing trail associated with the order.
     *
     * @return BelongsTo<JalurPendakian, Pesanan>
     */
    public function jalur(): BelongsTo
    {
        return $this->belongsTo(JalurPendakian::class, 'jalur_id');
    }

    /**
     * Get the registered members for this order (climbers).
     *
     * @return HasMany<PesananAnggota>
     */
    public function anggotas(): HasMany
    {
        return $this->hasMany(PesananAnggota::class, 'pesanan_id');
    }

    /**
     * Get the line items of this order.
     *
     * @return HasMany<DetailPesanan>
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id');
    }

    /**
     * Get the payment status and gateway transaction reference.
     *
     * @return HasOne<Pembayaran>
     */
    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'pesanan_id');
    }

    /**
     * Get refunds processed for this order.
     *
     * @return HasMany<Refund>
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'pesanan_id');
    }
}
