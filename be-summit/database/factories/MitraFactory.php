<?php

namespace Database\Factories;

use App\Models\Mitra;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mitra>
 */
class MitraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->mitra(),
            'nama_pemilik' => fake()->name(),
            'telepon' => fake()->phoneNumber(),
            'alamat' => fake()->address(),
            'deskripsi' => fake()->sentence(),
            'status' => 'aktif',
            'npwp' => fake()->unique()->numerify('###############'), // 15 digits
            'nik' => fake()->unique()->numerify('################'), // 16 digits
            'rekening_bank' => fake()->unique()->bankAccountNumber(),
            'nama_rekening' => fake()->name(),
            'bank' => fake()->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'ewallet' => null,
        ];
    }
}
