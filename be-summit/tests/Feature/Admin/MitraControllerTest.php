<?php

use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('admin can list mitras', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $userMitra = User::factory()->create(['role' => 'mitra', 'name' => 'Budi Santoso']);
    $mitra = Mitra::create([
        'user_id' => $userMitra->id,
        'nama_pemilik' => 'Budi Santoso',
        'telepon' => '081234567890',
        'alamat' => 'Jl. Raya Summit No. 10',
        'deskripsi' => 'Mitra Merbabu',
        'status' => 'aktif',
        'npwp' => '12.345.678.9-012.000',
        'nik' => '3201234567890001',
        'rekening_bank' => '1234567890',
        'nama_rekening' => 'Budi Santoso',
        'bank' => 'Bank BCA',
        'ewallet' => '081234567890',
    ]);

    $response = $this->actingAs($admin)->getJson(route('admin.mitra.index'));

    $response->assertStatus(200);
    $response->assertJsonPath('data.data.0.id', $mitra->id);
    $response->assertJsonPath('data.data.0.nama_pemilik', 'Budi Santoso');
});

test('admin can create a new mitra and user account', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->postJson(route('admin.mitra.store'), [
        'email' => 'newmitra@example.com',
        'password' => 'password123',
        'nama_pemilik' => 'Andi Wijaya',
        'telepon' => '081122334455',
        'alamat' => 'Jl. Merbabu Indah No. 5',
        'deskripsi' => 'Owner Basecamp Selo',
        'status' => 'aktif',
        'npwp' => '12.345.678.9-013.000',
        'nik' => '3201234567890002',
        'rekening_bank' => '0987654321',
        'nama_rekening' => 'Andi Wijaya',
        'bank' => 'Bank Mandiri',
        'ewallet' => '081122334455',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('users', [
        'email' => 'newmitra@example.com',
        'role' => 'mitra',
    ]);
    $this->assertDatabaseHas('mitras', [
        'nama_pemilik' => 'Andi Wijaya',
        'nik' => '3201234567890002',
    ]);
});

test('admin can update a mitra and user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $userMitra = User::factory()->create(['role' => 'mitra', 'name' => 'Budi Santoso', 'email' => 'budi@example.com']);
    $mitra = Mitra::create([
        'user_id' => $userMitra->id,
        'nama_pemilik' => 'Budi Santoso',
        'telepon' => '081234567890',
        'alamat' => 'Jl. Raya Summit No. 10',
        'deskripsi' => 'Mitra Merbabu',
        'status' => 'aktif',
        'npwp' => '12.345.678.9-012.000',
        'nik' => '3201234567890001',
        'rekening_bank' => '1234567890',
        'nama_rekening' => 'Budi Santoso',
        'bank' => 'Bank BCA',
        'ewallet' => '081234567890',
    ]);

    $response = $this->actingAs($admin)->putJson(route('admin.mitra.update', ['id' => $mitra->id]), [
        'email' => 'budi.updated@example.com',
        'password' => 'newpassword123',
        'nama_pemilik' => 'Budi Santoso Updated',
        'telepon' => '081234567891',
        'alamat' => 'Jl. Raya Summit No. 12',
        'deskripsi' => 'Mitra Merbabu Baru',
        'status' => 'suspend',
        'npwp' => '12.345.678.9-012.001',
        'nik' => '3201234567890009',
        'rekening_bank' => '1234567899',
        'nama_rekening' => 'Budi Santoso Updated',
        'bank' => 'Bank Mandiri',
        'ewallet' => '081234567891',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', [
        'id' => $userMitra->id,
        'email' => 'budi.updated@example.com',
        'name' => 'Budi Santoso Updated',
    ]);
    $this->assertDatabaseHas('mitras', [
        'id' => $mitra->id,
        'nama_pemilik' => 'Budi Santoso Updated',
        'status' => 'suspend',
        'nik' => '3201234567890009',
    ]);
});

test('admin can delete a mitra and its user account cascades', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $userMitra = User::factory()->create(['role' => 'mitra', 'name' => 'Budi Santoso']);
    $mitra = Mitra::create([
        'user_id' => $userMitra->id,
        'nama_pemilik' => 'Budi Santoso',
        'telepon' => '081234567890',
        'alamat' => 'Jl. Raya Summit No. 10',
        'status' => 'aktif',
        'nik' => '3201234567890001',
        'rekening_bank' => '1234567890',
        'nama_rekening' => 'Budi Santoso',
        'bank' => 'Bank BCA',
    ]);

    $response = $this->actingAs($admin)->deleteJson(route('admin.mitra.destroy', ['id' => $mitra->id]));

    $response->assertStatus(200);
    $this->assertDatabaseMissing('mitras', ['id' => $mitra->id]);
    $this->assertDatabaseMissing('users', ['id' => $userMitra->id]);
});

test('climber cannot perform CRUD operations on mitras', function () {
    $user = User::factory()->create(['role' => 'pendaki']);
    $user->markEmailAsVerified();

    $response = $this->actingAs($user)->getJson(route('admin.mitra.index'));
    $response->assertStatus(403);
});
