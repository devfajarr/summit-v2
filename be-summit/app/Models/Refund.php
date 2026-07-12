<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'pesanan_id',
    'pembayaran_id',
    'xendit_refund_id',
    'tipe',
    'nominal',
    'alasan',
    'status',
    'bank_tujuan',
    'rekening_tujuan',
    'nama_tujuan',
    'bukti_transfer',
    'raw_response',
    'refunded_at',
])]
class Refund extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'refunds';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'raw_response' => 'array',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * Get the order associated with the refund.
     *
     * @return BelongsTo<Pesanan, Refund>
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /**
     * Get the payment associated with the refund.
     *
     * @return BelongsTo<Pembayaran, Refund>
     */
    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }
}
