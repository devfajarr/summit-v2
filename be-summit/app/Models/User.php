<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if the user has the 'admin' role.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the OTPs generated for the user.
     *
     * @return HasMany<UserOtp>
     */
    public function otps(): HasMany
    {
        return $this->hasMany(UserOtp::class);
    }

    /**
     * Get the climber profile associated with the user.
     *
     * @return HasOne<Pendaki>
     */
    public function pendaki(): HasOne
    {
        return $this->hasOne(Pendaki::class, 'user_id');
    }

    /**
     * Get the partner profile associated with the user.
     *
     * @return HasOne<Mitra>
     */
    public function mitra(): HasOne
    {
        return $this->hasOne(Mitra::class, 'user_id');
    }

    /**
     * Get the orders placed by the user.
     *
     * @return HasMany<Pesanan>
     */
    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'user_id');
    }
}
