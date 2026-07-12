<?php

use App\Models\User;
use App\Models\Pendaki;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Database\QueryException;

uses(LazilyRefreshDatabase::class);

test('a user does not have a pendaki profile by default (supports skip verification)', function () {
    $user = User::factory()->create([
        'role' => 'pendaki',
    ]);

    expect($user->pendaki)->toBeNull();
});

test('a user can have a pendaki profile created with correct columns', function () {
    $user = User::factory()->create([
        'name' => 'Pendaki Ganteng',
        'role' => 'pendaki',
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    // Create pendaki profile using user name as default nama_lengkap
    $pendaki = Pendaki::create([
        'user_id' => $user->id,
        'nama_lengkap' => $user->name, // Menggunakan name pada table user sebagai referensi nama_lengkap
        'jenis_identitas' => 'ktp',
        'nomor_identitas' => '1234567890123456',
        'foto_identitas' => 'kyc/ktp/ganteng.jpg',
        'tanggal_lahir' => '2000-01-01',
        'jenis_kelamin' => 'l',
        'alamat' => 'Jl. Pegunungan No. 1',
        'telepon' => '08123456789',
        'nama_kontak_darurat' => 'Budi',
        'telepon_darurat' => '08129876543',
        'hubungan_darurat' => 'Orang Tua',
        'status_verifikasi' => 'pending',
    ]);

    expect($user->fresh()->pendaki)->not->toBeNull();
    expect($user->fresh()->pendaki->nama_lengkap)->toBe('Pendaki Ganteng');
    expect($user->fresh()->pendaki->status_verifikasi)->toBe('pending');

    // Admin verifies the profile
    $pendaki->update([
        'status_verifikasi' => 'disetujui',
        'verified_at' => now(),
        'verified_by' => $admin->id,
    ]);

    expect($pendaki->fresh()->status_verifikasi)->toBe('disetujui');
    expect($pendaki->fresh()->verifiedBy->id)->toBe($admin->id);
});

test('nomor_identitas must be unique', function () {
    $user1 = User::factory()->create(['role' => 'pendaki']);
    $user2 = User::factory()->create(['role' => 'pendaki']);

    Pendaki::create([
        'user_id' => $user1->id,
        'nama_lengkap' => $user1->name,
        'jenis_identitas' => 'ktp',
        'nomor_identitas' => 'same-identity-number',
        'foto_identitas' => 'kyc/ktp/user1.jpg',
        'tanggal_lahir' => '2000-01-01',
        'jenis_kelamin' => 'l',
        'alamat' => 'Alamat 1',
        'telepon' => '08123456789',
        'nama_kontak_darurat' => 'Budi',
        'telepon_darurat' => '08129876543',
        'hubungan_darurat' => 'Orang Tua',
        'status_verifikasi' => 'pending',
    ]);

    // This should fail due to unique constraint on nomor_identitas
    $this->expectException(QueryException::class);

    Pendaki::create([
        'user_id' => $user2->id,
        'nama_lengkap' => $user2->name,
        'jenis_identitas' => 'paspor',
        'nomor_identitas' => 'same-identity-number',
        'foto_identitas' => 'kyc/paspor/user2.jpg',
        'tanggal_lahir' => '1999-12-31',
        'jenis_kelamin' => 'p',
        'alamat' => 'Alamat 2',
        'telepon' => '08123456780',
        'nama_kontak_darurat' => 'Siti',
        'telepon_darurat' => '08129876544',
        'hubungan_darurat' => 'Ibu',
        'status_verifikasi' => 'pending',
    ]);
});
