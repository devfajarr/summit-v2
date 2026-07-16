<?php

use App\Models\Mitra;
use App\Models\Pendaki;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

test('guest cannot access profile details', function () {
    $response = $this->getJson(route('profile.show'));
    $response->assertStatus(401);
});

test('admin can retrieve their profile details', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'name' => 'Super Admin',
    ]);
    $admin->markEmailAsVerified();

    $response = $this->actingAs($admin)
        ->getJson(route('profile.show'));

    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'success',
        'message' => 'Profile retrieved successfully.',
        'data' => [
            'id' => $admin->id,
            'name' => 'Super Admin',
            'email' => $admin->email,
            'role' => 'admin',
        ],
    ]);

    // Ensure pendaki and mitra relations are not present
    $response->assertJsonMissingPath('data.pendaki');
    $response->assertJsonMissingPath('data.mitra');
});

test('pendaki can retrieve their profile details along with pendaki information', function () {
    $user = User::factory()->create([
        'role' => 'pendaki',
        'name' => 'Budi Pendaki',
    ]);
    $user->markEmailAsVerified();

    $pendaki = Pendaki::create([
        'user_id' => $user->id,
        'nama_lengkap' => 'Budi Pendaki Fullname',
        'jenis_identitas' => 'ktp',
        'nomor_identitas' => '1234567890123456',
        'foto_identitas' => 'kyc/ktp/budi.jpg',
        'tanggal_lahir' => '2000-01-01',
        'jenis_kelamin' => 'l',
        'alamat' => 'Jl. Pendaki No. 12',
        'telepon' => '08123456789',
        'nama_kontak_darurat' => 'Budi Emergency',
        'telepon_darurat' => '08129876543',
        'hubungan_darurat' => 'Orang Tua',
        'status_verifikasi' => 'pending',
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('profile.show'));

    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'success',
        'message' => 'Profile retrieved successfully.',
        'data' => [
            'id' => $user->id,
            'name' => 'Budi Pendaki',
            'role' => 'pendaki',
            'pendaki' => [
                'id' => $pendaki->id,
                'nama_lengkap' => 'Budi Pendaki Fullname',
                'nomor_identitas' => '1234567890123456',
                'jenis_identitas' => 'ktp',
            ],
        ],
    ]);
    $response->assertJsonMissingPath('data.mitra');
});

test('mitra can retrieve their profile details along with mitra information', function () {
    $user = User::factory()->create([
        'role' => 'mitra',
        'name' => 'Mitra Sentosa',
    ]);
    $user->markEmailAsVerified();

    $mitra = Mitra::create([
        'user_id' => $user->id,
        'nama_pemilik' => 'Mitra Sentosa Pemilik',
        'telepon' => '08567890123',
        'alamat' => 'Ruko Sentosa Indah No. 5',
        'nik' => '3201234567890099',
        'rekening_bank' => '9876543210',
        'nama_rekening' => 'Mitra Sentosa',
        'bank' => 'Bank Mandiri',
        'status' => 'aktif',
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('profile.show'));

    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'success',
        'message' => 'Profile retrieved successfully.',
        'data' => [
            'id' => $user->id,
            'name' => 'Mitra Sentosa',
            'role' => 'mitra',
            'mitra' => [
                'id' => $mitra->id,
                'nama_pemilik' => 'Mitra Sentosa Pemilik',
                'nik' => '3201234567890099',
                'rekening_bank' => '9876543210',
            ],
        ],
    ]);
    $response->assertJsonMissingPath('data.pendaki');
});

test('guest cannot change password', function () {
    $response = $this->putJson(route('profile.password.update'), [
        'current_password' => 'password',
        'new_password' => 'NewPassword123!',
        'new_password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(401);
});

test('user can change their password successfully', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);
    $user->markEmailAsVerified();

    $response = $this->actingAs($user)
        ->putJson(route('profile.password.update'), [
            'current_password' => 'OldPassword123!',
            'new_password' => 'NewPassword123!',
            'new_password_confirmation' => 'NewPassword123!',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'success',
        'message' => 'Password updated successfully.',
    ]);

    $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
});

test('user cannot change password with incorrect current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);
    $user->markEmailAsVerified();

    $response = $this->actingAs($user)
        ->putJson(route('profile.password.update'), [
            'current_password' => 'WrongOldPassword123!',
            'new_password' => 'NewPassword123!',
            'new_password_confirmation' => 'NewPassword123!',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['current_password']);
});

test('user cannot change password if new password does not meet security rules', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);
    $user->markEmailAsVerified();

    // 1. Weak password (too short, no numbers/symbols/mixedCase)
    $response = $this->actingAs($user)
        ->putJson(route('profile.password.update'), [
            'current_password' => 'OldPassword123!',
            'new_password' => 'simple',
            'new_password_confirmation' => 'simple',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['new_password']);

    // 2. Confirmed password mismatch
    $response = $this->actingAs($user)
        ->putJson(route('profile.password.update'), [
            'current_password' => 'OldPassword123!',
            'new_password' => 'NewPassword123!',
            'new_password_confirmation' => 'Different123!',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['new_password']);
});
