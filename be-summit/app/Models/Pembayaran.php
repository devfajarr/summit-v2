<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'pesanan_id',
    'metode',
    'provider',
    'xendit_invoice_id',
    'checkout_url',
    'status',
    'biaya_gateway',
    'raw_response',
    'paid_at',
    'expired_at',
])]
class Pembayaran extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pembayarans';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'biaya_gateway' => 'decimal:2',
            'raw_response' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    /**
     * Get the parent order.
     *
     * @return BelongsTo<Pesanan, Pembayaran>
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /**
     * Get the refunds processed for this payment.
     *
     * @return HasMany<Refund>
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'pembayaran_id');
    }
}
