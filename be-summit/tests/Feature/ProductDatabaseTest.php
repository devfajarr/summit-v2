<?php

use App\Models\Basecamp;
use App\Models\Gunung;
use App\Models\JalurPendakian;
use App\Models\KuotaHarianTiket;
use App\Models\Mitra;
use App\Models\Produk;
use App\Models\ProdukOpentrip;
use App\Models\ProdukTiket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
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

    $userMitra = User::factory()->create(['role' => 'mitra', 'name' => 'Budi Santoso']);
    $this->mitra = Mitra::create([
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

    $this->basecamp = Basecamp::create([
        'mitra_id' => $this->mitra->id,
        'jalur_id' => $this->jalur->id,
        'nama_basecamp' => 'Basecamp Cibodas Indah',
        'latitude' => '-6.793214',
        'longitude' => '107.001234',
        'jam_operasional' => '24 Jam',
    ]);
});

test('can create a basic product and associate with basecamp', function () {
    $produk = Produk::create([
        'basecamp_id' => $this->basecamp->id,
        'nama_produk' => 'Sewa Sleeping Bag',
        'kategori' => 'rental',
        'deskripsi' => 'Sleeping bag hangat tahan hingga 0 derajat Celsius.',
        'harga' => 15000.00,
        'stok' => 20,
        'satuan' => 'hari',
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('produks', [
        'id' => $produk->id,
        'nama_produk' => 'Sewa Sleeping Bag',
        'basecamp_id' => $this->basecamp->id,
    ]);

    // Check relationship
    expect($this->basecamp->produks)->toHaveCount(1);
    expect($this->basecamp->produks->first()->nama_produk)->toBe('Sewa Sleeping Bag');
    expect($produk->basecamp->id)->toBe($this->basecamp->id);
});

test('can create open trip details for a product', function () {
    $produk = Produk::create([
        'basecamp_id' => $this->basecamp->id,
        'nama_produk' => 'Open Trip Merbabu 3D2N',
        'kategori' => 'opentrip',
        'deskripsi' => 'Paket open trip lengkap dari basecamp.',
        'harga' => 450000.00,
        'is_active' => true,
    ]);

    $opentrip = ProdukOpentrip::create([
        'produk_id' => $produk->id,
        'tanggal_berangkat' => '2026-08-01',
        'tanggal_pulang' => '2026-08-03',
        'meeting_point' => 'Basecamp Selo',
        'minimal_peserta' => 5,
        'maksimal_peserta' => 15,
        'sisa_kursi' => 15,
    ]);

    $this->assertDatabaseHas('produk_opentrips', [
        'produk_id' => $produk->id,
        'meeting_point' => 'Basecamp Selo',
    ]);

    // Verify relations
    expect($produk->fresh()->opentrip->id)->toBe($opentrip->id);
    expect($opentrip->produk->id)->toBe($produk->id);
});

test('can create ticket details and daily quotas', function () {
    $produk = Produk::create([
        'basecamp_id' => $this->basecamp->id,
        'nama_produk' => 'Simaksi Cibodas',
        'kategori' => 'ticket',
        'deskripsi' => 'Tiket izin pendakian resmi via Cibodas.',
        'harga' => 20000.00,
        'is_active' => true,
    ]);

    $tiket = ProdukTiket::create([
        'produk_id' => $produk->id,
        'jalur_id' => $this->jalur->id,
        'jam_buka' => '07:00:00',
        'jam_tutup' => '17:00:00',
    ]);

    $kuota = KuotaHarianTiket::create([
        'produk_tiket_id' => $tiket->id,
        'tanggal' => '2026-07-10',
        'kuota_total' => 100,
        'kuota_tersisa' => 100,
    ]);

    $this->assertDatabaseHas('produk_tikets', [
        'produk_id' => $produk->id,
        'jalur_id' => $this->jalur->id,
    ]);

    $this->assertDatabaseHas('kuota_harian_tikets', [
        'produk_tiket_id' => $tiket->id,
        'kuota_total' => 100,
    ]);

    // Verify relations
    expect($produk->fresh()->tiket->id)->toBe($tiket->id);
    expect($tiket->jalur->id)->toBe($this->jalur->id);
    expect($tiket->kuotas)->toHaveCount(1);
    expect($tiket->kuotas->first()->id)->toBe($kuota->id);
    expect($kuota->tiket->id)->toBe($tiket->id);
    expect($kuota->tanggal->format('Y-m-d'))->toBe('2026-07-10');

    // Verify siblings
    expect($this->jalur->produkTikets)->toHaveCount(1);
});

test('deleting a basecamp cascades and deletes associated products', function () {
    $produk = Produk::create([
        'basecamp_id' => $this->basecamp->id,
        'nama_produk' => 'Sewa Sleeping Bag',
        'kategori' => 'rental',
        'deskripsi' => 'Sleeping bag hangat.',
        'harga' => 15000.00,
        'is_active' => true,
    ]);

    $this->basecamp->delete();

    $this->assertDatabaseMissing('produks', ['id' => $produk->id]);
});
