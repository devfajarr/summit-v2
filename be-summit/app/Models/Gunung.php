<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_gunung', 'deskripsi', 'tinggi_mdpl', 'lokasi', 'foto', 'status'])]
class Gunung extends Model
{
    /**
     * Get the trails for the mountain.
     *
     * @return HasMany<JalurPendakian>
     */
    public function jalurs(): HasMany
    {
        return $this->hasMany(JalurPendakian::class, 'gunung_id');
    }
}
