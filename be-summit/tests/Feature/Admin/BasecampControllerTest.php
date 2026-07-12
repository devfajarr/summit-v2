<?php

use App\Models\Basecamp;
use App\Models\Gunung;
use App\Models\JalurPendakian;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);

    $this->gunung = Gunung::create([
        'nama_gunung' => 'Gunung Gede',
        'deskripsi' => 'Gunung Jawa Barat.',
        'tinggi_mdpl' => 2958,
        'lokasi' => 'Cianjur, Jawa Barat',
        'foto' => 'gunungs/gede.jpg',
        'status' => 'aktif',
    ]);

    $this->jalur = JalurPendakian::create([
        'gunung_id' => $this->gunung->id,
        'nama_jalur' => 'Jalur Cibodas',
        'deskripsi' => 'Jalur air terjun dan air panas.',
        'titik_awal_mdpl' => '1300 MDPL',
        'titik_akhir_mdpl' => '2958 MDPL',
        'waktu_tempuh' => '7 Jam',
        'status' => 'open',
        'panjang_jalur' => '9.7 Km',
        'tingkat_kesulitan' => 'sedang',
    ]);

    $this->userMitra = User::factory()->create(['role' => 'mitra', 'name' => 'Budi Santoso']);
    $this->mitra = Mitra::create([
        'user_id' => $this->userMitra->id,
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
});

test('admin can list basecamps', function () {
    $basecamp = Basecamp::create([
        'mitra_id' => $this->mitra->id,
        'jalur_id' => $this->jalur->id,
        'nama_basecamp' => 'Basecamp Cibodas Indah',
        'latitude' => '-6.793214',
        'longitude' => '107.001234',
        'jam_operasional' => '24 Jam',
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.basecamp.index'));

    $response->assertStatus(200);
    $response->assertJsonPath('data.data.0.id', $basecamp->id);
    $response->assertJsonPath('data.data.0.nama_basecamp', 'Basecamp Cibodas Indah');
});

test('admin can create a basecamp', function () {
    $response = $this->actingAs($this->admin)->postJson(route('admin.basecamp.store'), [
        'mitra_id' => $this->mitra->id,
        'jalur_id' => $this->jalur->id,
        'nama_basecamp' => 'Basecamp Cibodas Baru',
        'latitude' => '-6.793215',
        'longitude' => '107.001235',
        'jam_operasional' => '08:00 - 22:00',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('basecamps', [
        'nama_basecamp' => 'Basecamp Cibodas Baru',
        'mitra_id' => $this->mitra->id,
        'jalur_id' => $this->jalur->id,
    ]);
});

test('admin can update a basecamp', function () {
    $basecamp = Basecamp::create([
        'mitra_id' => $this->mitra->id,
        'jalur_id' => $this->jalur->id,
        'nama_basecamp' => 'Basecamp Cibodas Indah',
        'latitude' => '-6.793214',
        'longitude' => '107.001234',
        'jam_operasional' => '24 Jam',
    ]);

    $response = $this->actingAs($this->admin)->putJson(route('admin.basecamp.update', ['id' => $basecamp->id]), [
        'mitra_id' => $this->mitra->id,
        'jalur_id' => $this->jalur->id,
        'nama_basecamp' => 'Basecamp Cibodas Indah Updated',
        'latitude' => '-6.793299',
        'longitude' => '107.001299',
        'jam_operasional' => '07:00 - 23:00',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('basecamps', [
        'id' => $basecamp->id,
        'nama_basecamp' => 'Basecamp Cibodas Indah Updated',
        'jam_operasional' => '07:00 - 23:00',
    ]);
});

test('admin can delete a basecamp', function () {
    $basecamp = Basecamp::create([
        'mitra_id' => $this->mitra->id,
        'jalur_id' => $this->jalur->id,
        'nama_basecamp' => 'Basecamp Cibodas Indah',
        'latitude' => '-6.793214',
        'longitude' => '107.001234',
        'jam_operasional' => '24 Jam',
    ]);

    $response = $this->actingAs($this->admin)->deleteJson(route('admin.basecamp.destroy', ['id' => $basecamp->id]));

    $response->assertStatus(200);
    $this->assertDatabaseMissing('basecamps', ['id' => $basecamp->id]);
});

test('climber cannot perform CRUD operations on basecamps', function () {
    $user = User::factory()->create(['role' => 'pendaki']);
    $user->markEmailAsVerified();

    $response = $this->actingAs($user)->getJson(route('admin.basecamp.index'));
    $response->assertStatus(403);
});
