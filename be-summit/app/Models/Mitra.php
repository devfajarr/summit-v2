<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'nama_pemilik',
    'telepon',
    'alamat',
    'deskripsi',
    'status',
    'npwp',
    'nik',
    'rekening_bank',
    'nama_rekening',
    'bank',
    'ewallet',
])]
class Mitra extends Model
{
    use HasFactory;

    /**
     * Get the user associated with the partner.
     *
     * @return BelongsTo<User, Mitra>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the basecamps owned by the partner.
     *
     * @return HasMany<Basecamp>
     */
    public function basecamps(): HasMany
    {
        return $this->hasMany(Basecamp::class, 'mitra_id');
    }
}
