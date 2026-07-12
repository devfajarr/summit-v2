<?php

use App\Models\Gunung;
use App\Models\JalurPendakian;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('admin can create a trail for a mountain', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $gunung = Gunung::create([
        'nama_gunung' => 'Gunung Gede',
        'deskripsi' => 'Gunung Jawa Barat.',
        'tinggi_mdpl' => 2958,
        'lokasi' => 'Cianjur, Jawa Barat',
        'foto' => 'gunungs/gede.jpg',
        'status' => 'aktif',
    ]);

    $response = $this->actingAs($admin)->postJson(route('admin.jalur.store'), [
        'gunung_id' => $gunung->id,
        'nama_jalur' => 'Jalur Cibodas',
        'deskripsi' => 'Jalur air terjun dan air panas.',
        'titik_awal_mdpl' => '1300 MDPL',
        'titik_akhir_mdpl' => '2958 MDPL',
        'waktu_tempuh' => '7 Jam',
        'status' => 'open',
        'panjang_jalur' => '9.7 Km',
        'tingkat_kesulitan' => 'sedang',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('jalur_pendakians', [
        'nama_jalur' => 'Jalur Cibodas',
        'gunung_id' => $gunung->id,
    ]);
});

test('admin can update a trail', function () {
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

    $response = $this->actingAs($admin)->putJson(route('admin.jalur.update', ['id' => $jalur->id]), [
        'gunung_id' => $gunung->id,
        'nama_jalur' => 'Jalur Putri Updated',
        'deskripsi' => 'Jalur terjal dengan pemandangan luas.',
        'titik_awal_mdpl' => '1400 MDPL',
        'titik_akhir_mdpl' => '2958 MDPL',
        'waktu_tempuh' => '6 Jam',
        'status' => 'close',
        'panjang_jalur' => '6 Km',
        'tingkat_kesulitan' => 'sulit',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('jalur_pendakians', [
        'id' => $jalur->id,
        'nama_jalur' => 'Jalur Putri Updated',
        'status' => 'close',
    ]);
});

test('admin can delete a trail', function () {
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
        'nama_jalur' => 'Jalur Salabintana',
        'deskripsi' => 'Jalur pacet.',
        'titik_awal_mdpl' => '1000 MDPL',
        'titik_akhir_mdpl' => '2958 MDPL',
        'waktu_tempuh' => '12 Jam',
        'status' => 'open',
        'panjang_jalur' => '12 Km',
        'tingkat_kesulitan' => 'ekstrem',
    ]);

    $response = $this->actingAs($admin)->deleteJson(route('admin.jalur.destroy', ['id' => $jalur->id]));

    $response->assertStatus(200);
    $this->assertDatabaseMissing('jalur_pendakians', [
        'id' => $jalur->id,
    ]);
});

test('climber cannot perform CRUD operations on trails', function () {
    $user = User::factory()->create(['role' => 'pendaki']);
    $user->markEmailAsVerified();

    $response = $this->actingAs($user)->postJson(route('admin.jalur.store'), [
        'nama_jalur' => 'Jalur Ilegal',
    ]);
    $response->assertStatus(403);
});
