<?php

namespace App\Policies;

use App\Models\Produk;
use App\Models\User;

class ProdukPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Produk $produk): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->role === 'pendaki') {
            return (bool) $produk->is_active;
        }

        if ($user->role === 'mitra') {
            return $user->mitra && $produk->basecamp->mitra_id === $user->mitra->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'mitra';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Produk $produk): bool
    {
        return $user->role === 'mitra'
            && $user->mitra
            && $produk->basecamp->mitra_id === $user->mitra->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Produk $produk): bool
    {
        return $user->role === 'mitra'
            && $user->mitra
            && $produk->basecamp->mitra_id === $user->mitra->id;
    }
}
