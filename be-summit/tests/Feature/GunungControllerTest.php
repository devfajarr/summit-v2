<?php

use App\Models\Gunung;
use App\Models\JalurPendakian;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

test('climber can view list of mountains with eager loaded jalurs', function () {
    $user = User::factory()->create(['role' => 'pendaki']);
    $user->markEmailAsVerified();

    $gunung = Gunung::create([
        'nama_gunung' => 'Gunung Semeru',
        'deskripsi' => 'Gunung tertinggi di Pulau Jawa.',
        'tinggi_mdpl' => 3676,
        'lokasi' => 'Lumajang/Malang, Jawa Timur',
        'foto' => 'gunungs/semeru.jpg',
        'status' => 'aktif',
    ]);

    $jalur = JalurPendakian::create([
        'gunung_id' => $gunung->id,
        'nama_jalur' => 'Jalur Ranu Pane',
        'deskripsi' => 'Jalur pendakian utama.',
        'titik_awal_mdpl' => '2100 MDPL',
        'titik_akhir_mdpl' => '3676 MDPL',
        'waktu_tempuh' => '4 Hari',
        'status' => 'open',
        'panjang_jalur' => '15 Km',
        'tingkat_kesulitan' => 'sedang',
    ]);

    $response = $this->actingAs($user)->getJson(route('gunung.index'));

    $response->assertStatus(200);
    $response->assertJsonPath('data.data.0.nama_gunung', 'Gunung Semeru');
    $response->assertJsonPath('data.data.0.jalurs.0.nama_jalur', 'Jalur Ranu Pane');
});

test('climber can view detail of a mountain', function () {
    $user = User::factory()->create(['role' => 'pendaki']);
    $user->markEmailAsVerified();

    $gunung = Gunung::create([
        'nama_gunung' => 'Gunung Gede',
        'deskripsi' => 'Gunung populer di Jawa Barat.',
        'tinggi_mdpl' => 2958,
        'lokasi' => 'Cianjur, Jawa Barat',
        'foto' => 'gunungs/gede.jpg',
        'status' => 'aktif',
    ]);

    $response = $this->actingAs($user)->getJson(route('gunung.show', ['id' => $gunung->id]));

    $response->assertStatus(200);
    $response->assertJsonPath('data.nama_gunung', 'Gunung Gede');
});

test('climber cannot perform CRUD operations on mountains', function () {
    $user = User::factory()->create(['role' => 'pendaki']);
    $user->markEmailAsVerified();

    $response = $this->actingAs($user)->postJson(route('admin.gunung.store'), [
        'nama_gunung' => 'Gunung Bromo',
    ]);
    $response->assertStatus(403);
});

test('admin can create a mountain', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $file = UploadedFile::fake()->image('bromo.jpg');

    $response = $this->actingAs($admin)->postJson(route('admin.gunung.store'), [
        'nama_gunung' => 'Gunung Bromo',
        'deskripsi' => 'Gunung wisata yang indah.',
        'tinggi_mdpl' => 2329,
        'lokasi' => 'Probolinggo, Jawa Timur',
        'foto' => $file,
        'status' => 'aktif',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('gunungs', [
        'nama_gunung' => 'Gunung Bromo',
    ]);

    $gunung = Gunung::where('nama_gunung', 'Gunung Bromo')->first();
    Storage::disk('public')->assertExists($gunung->foto);
});

test('admin can update a mountain', function () {
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

    $newFile = UploadedFile::fake()->image('salak_baru.jpg');

    // Use POST with _method=PUT to update with file attachment
    $response = $this->actingAs($admin)->postJson(route('admin.gunung.update', ['id' => $gunung->id]), [
        '_method' => 'PUT',
        'nama_gunung' => 'Gunung Salak Updated',
        'deskripsi' => 'Gunung mistis di Bogor.',
        'tinggi_mdpl' => 2211,
        'lokasi' => 'Bogor, Jawa Barat',
        'foto' => $newFile,
        'status' => 'aktif',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('gunungs', [
        'id' => $gunung->id,
        'nama_gunung' => 'Gunung Salak Updated',
    ]);

    Storage::disk('public')->assertExists($gunung->fresh()->foto);
});

test('admin can delete a mountain', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $gunung = Gunung::create([
        'nama_gunung' => 'Gunung Cikuray',
        'deskripsi' => 'Gunung tertinggi keempat di Jawa Barat.',
        'tinggi_mdpl' => 2821,
        'lokasi' => 'Garut, Jawa Barat',
        'foto' => 'gunungs/cikuray.jpg',
        'status' => 'aktif',
    ]);

    $response = $this->actingAs($admin)->deleteJson(route('admin.gunung.destroy', ['id' => $gunung->id]));

    $response->assertStatus(200);
    $this->assertDatabaseMissing('gunungs', [
        'id' => $gunung->id,
    ]);
});
