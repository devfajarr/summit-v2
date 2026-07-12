<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'nama_lengkap',
    'jenis_identitas',
    'nomor_identitas',
    'foto_identitas',
    'tanggal_lahir',
    'jenis_kelamin',
    'alamat',
    'telepon',
    'nama_kontak_darurat',
    'telepon_darurat',
    'hubungan_darurat',
    'status_verifikasi',
    'alasan_penolakan',
    'verified_at',
    'verified_by',
])]
class Pendaki extends Model
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
            'tanggal_lahir' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Get the user (climber) that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin user who verified the KYC document.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
