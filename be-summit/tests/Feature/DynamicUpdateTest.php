<?php

use App\Models\Gunung;
use App\Models\JalurPendakian;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

test('PUT request for Gunung with incomplete fields fails with validation error', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $gunung = Gunung::create([
        'nama_gunung' => 'Gunung Gede',
        'deskripsi' => 'Gunung di Jawa Barat.',
        'tinggi_mdpl' => 2958,
        'lokasi' => 'Cianjur, Jawa Barat',
        'foto' => 'gunungs/gede.jpg',
        'status' => 'aktif',
    ]);

    // Send PUT request with missing fields (deskripsi, tinggi_mdpl, etc. are missing)
    $response = $this->actingAs($admin)->putJson(route('admin.gunung.update', ['id' => $gunung->id]), [
        'nama_gunung' => 'Gunung Gede Updated',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['deskripsi', 'tinggi_mdpl', 'lokasi', 'status']);
});

test('PATCH request for Gunung with incomplete fields updates successfully', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $gunung = Gunung::create([
        'nama_gunung' => 'Gunung Gede',
        'deskripsi' => 'Gunung di Jawa Barat.',
        'tinggi_mdpl' => 2958,
        'lokasi' => 'Cianjur, Jawa Barat',
        'foto' => 'gunungs/gede.jpg',
        'status' => 'aktif',
    ]);

    // Send PATCH request with only nama_gunung
    $response = $this->actingAs($admin)->patchJson(route('admin.gunung.update', ['id' => $gunung->id]), [
        'nama_gunung' => 'Gunung Gede Patched',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('gunungs', [
        'id' => $gunung->id,
        'nama_gunung' => 'Gunung Gede Patched',
        'deskripsi' => 'Gunung di Jawa Barat.', // Untouched
    ]);
});

test('PUT request for Jalur with incomplete fields fails with validation error', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $gunung = Gunung::create([
        'nama_gunung' => 'Gunung Gede',
        'deskripsi' => 'Gunung Jawa Barat.',
        'tinggi_mdpl' => 2958,
        'lokasi' => 'Cianjur, Jawa Barat',
        'foto' => 'gunungs/gede.jpg',
        'status' => 'aktif',
    ]);

    $jalur = JalurPendakian::create([
        'gunung_id' => $gunung->id,
        'nama_jalur' => 'Jalur Putri',
        'deskripsi' => 'Jalur curam.',
        'titik_awal_mdpl' => '1400 MDPL',
        'titik_akhir_mdpl' => '2958 MDPL',
        'waktu_tempuh' => '6 Jam',
        'status' => 'open',
        'panjang_jalur' => '6 Km',
        'tingkat_kesulitan' => 'sulit',
    ]);

    // Send PUT request with missing fields
    $response = $this->actingAs($admin)->putJson(route('admin.jalur.update', ['id' => $jalur->id]), [
        'gunung_id' => $gunung->id,
        'nama_jalur' => 'Jalur Putri Updated',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['deskripsi', 'status', 'tingkat_kesulitan']);
});

test('PATCH request for Jalur with incomplete fields updates successfully', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $gunung = Gunung::create([
        'nama_gunung' => 'Gunung Gede',
        'deskripsi' => 'Gunung Jawa Barat.',
        'tinggi_mdpl' => 2958,
        'lokasi' => 'Cianjur, Jawa Barat',
        'foto' => 'gunungs/gede.jpg',
        'status' => 'aktif',
    ]);

    $jalur = JalurPendakian::create([
        'gunung_id' => $gunung->id,
        'nama_jalur' => 'Jalur Putri',
        'deskripsi' => 'Jalur curam.',
        'titik_awal_mdpl' => '1400 MDPL',
        'titik_akhir_mdpl' => '2958 MDPL',
        'waktu_tempuh' => '6 Jam',
        'status' => 'open',
        'panjang_jalur' => '6 Km',
        'tingkat_kesulitan' => 'sulit',
    ]);

    // Send PATCH request with only status update
    $response = $this->actingAs($admin)->patchJson(route('admin.jalur.update', ['id' => $jalur->id]), [
        'gunung_id' => $gunung->id,
        'status' => 'close',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('jalur_pendakians', [
        'id' => $jalur->id,
        'status' => 'close',
        'nama_jalur' => 'Jalur Putri', // Untouched
    ]);
});

test('POST method override with _method = PATCH for Gunung updates successfully', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $gunung = Gunung::create([
        'nama_gunung' => 'Gunung Salak',
        'deskripsi' => 'Gunung di Bogor.',
        'tinggi_mdpl' => 2211,
        'lokasi' => 'Bogor, Jawa Barat',
        'foto' => 'gunungs/salak.jpg',
        'status' => 'aktif',
    ]);

    $newFile = UploadedFile::fake()->image('salak_patched.jpg');

    $response = $this->actingAs($admin)->postJson(route('admin.gunung.update', ['id' => $gunung->id]), [
        '_method' => 'PATCH',
        'foto' => $newFile,
    ]);

    $response->assertStatus(200);
    Storage::disk('public')->assertExists($gunung->fresh()->foto);
    $this->assertDatabaseHas('gunungs', [
        'id' => $gunung->id,
        'nama_gunung' => 'Gunung Salak', // Untouched
    ]);
});
