<?php

namespace App\Policies;

use App\Models\Pesanan;
use App\Models\User;

class PesananPolicy
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
    public function view(User $user, Pesanan $pesanan): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->role === 'pendaki') {
            return $user->id === $pesanan->user_id;
        }

        if ($user->role === 'mitra') {
            return $user->mitra && $pesanan->basecamp->mitra_id === $user->mitra->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'pendaki';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pesanan $pesanan): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->role === 'mitra') {
            return $user->mitra && $pesanan->basecamp->mitra_id === $user->mitra->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pesanan $pesanan): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pesanan $pesanan): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pesanan $pesanan): bool
    {
        return $user->isAdmin();
    }
}
