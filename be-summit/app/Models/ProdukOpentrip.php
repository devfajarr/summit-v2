<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'produk_id',
    'tanggal_berangkat',
    'tanggal_pulang',
    'meeting_point',
    'minimal_peserta',
    'maksimal_peserta',
    'sisa_kursi',
])]
class ProdukOpentrip extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'produk_opentrips';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_berangkat' => 'date',
            'tanggal_pulang' => 'date',
            'minimal_peserta' => 'integer',
            'maksimal_peserta' => 'integer',
            'sisa_kursi' => 'integer',
        ];
    }

    /**
     * Get the base product profile.
     *
     * @return BelongsTo<Produk, ProdukOpentrip>
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
