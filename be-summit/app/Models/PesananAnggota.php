<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'pesanan_id',
    'nama_anggota',
    'nik_identitas',
    'telepon',
    'telepon_darurat',
    'hubungan_darurat',
])]
class PesananAnggota extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pesanan_anggotas';

    /**
     * Get the order that this member belongs to.
     *
     * @return BelongsTo<Pesanan, PesananAnggota>
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }
}
