<?php

use App\Models\Basecamp;
use App\Models\Gunung;
use App\Models\JalurPendakian;
use App\Models\Mitra;
use App\Models\Produk;
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

    // Create Mitra A
    $userMitraA = User::factory()->create(['role' => 'mitra', 'name' => 'Mitra A']);
    $this->mitraA = Mitra::create([
        'user_id' => $userMitraA->id,
        'nama_pemilik' => 'Mitra A',
        'telepon' => '081234567890',
        'alamat' => 'Jl. Raya Summit No. 10',
        'status' => 'aktif',
        'nik' => '3201234567890001',
        'rekening_bank' => '1234567890',
        'nama_rekening' => 'Mitra A',
        'bank' => 'Bank BCA',
    ]);

    $this->basecampA = Basecamp::create([
        'mitra_id' => $this->mitraA->id,
        'jalur_id' => $this->jalur->id,
        'nama_basecamp' => 'Basecamp A',
        'latitude' => '-6.793214',
        'longitude' => '107.001234',
        'jam_operasional' => '24 Jam',
    ]);

    // Create Mitra B
    $userMitraB = User::factory()->create(['role' => 'mitra', 'name' => 'Mitra B']);
    $this->mitraB = Mitra::create([
        'user_id' => $userMitraB->id,
        'nama_pemilik' => 'Mitra B',
        'telepon' => '081234567891',
        'alamat' => 'Jl. Raya Summit No. 20',
        'status' => 'aktif',
        'nik' => '3201234567890002',
        'rekening_bank' => '1234567891',
        'nama_rekening' => 'Mitra B',
        'bank' => 'Bank BCA',
    ]);

    $this->basecampB = Basecamp::create([
        'mitra_id' => $this->mitraB->id,
        'jalur_id' => $this->jalur->id,
        'nama_basecamp' => 'Basecamp B',
        'latitude' => '-6.793215',
        'longitude' => '107.001235',
        'jam_operasional' => '24 Jam',
    ]);
});

test('mitra can list their own products', function () {
    $produk = Produk::create([
        'basecamp_id' => $this->basecampA->id,
        'nama_produk' => 'Rental Tenda A',
        'kategori' => 'rental',
        'harga' => 35000,
        'stok' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->mitraA->user)->getJson(route('mitra.products.index'));

    $response->assertStatus(200);
    $response->assertJsonPath('data.data.0.id', $produk->id);
    $response->assertJsonPath('data.data.0.nama_produk', 'Rental Tenda A');
});

test('mitra cannot see products of another mitra', function () {
    $produkB = Produk::create([
        'basecamp_id' => $this->basecampB->id,
        'nama_produk' => 'Rental Tenda B',
        'kategori' => 'rental',
        'harga' => 40000,
        'stok' => 5,
        'is_active' => true,
    ]);

    // Mitra A tries to show product B
    $response = $this->actingAs($this->mitraA->user)->getJson(route('mitra.products.show', ['id' => $produkB->id]));
    $response->assertStatus(403);
});

test('mitra can create rental product for their basecamp', function () {
    $response = $this->actingAs($this->mitraA->user)->postJson(route('mitra.products.store'), [
        'basecamp_id' => $this->basecampA->id,
        'nama_produk' => 'Sewa Carrier 60L',
        'kategori' => 'rental',
        'harga' => 25000,
        'stok' => 10,
        'satuan' => 'hari',
        'is_active' => true,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('produks', [
        'nama_produk' => 'Sewa Carrier 60L',
        'basecamp_id' => $this->basecampA->id,
    ]);
});

test('mitra cannot create product for another mitras basecamp', function () {
    // Mitra A tries to create product for Basecamp B
    $response = $this->actingAs($this->mitraA->user)->postJson(route('mitra.products.store'), [
        'basecamp_id' => $this->basecampB->id,
        'nama_produk' => 'Illegal Product',
        'kategori' => 'rental',
        'harga' => 10000,
        'stok' => 1,
    ]);

    $response->assertStatus(422); // Validation fails because basecamp_id doesn't belong to Mitra A
});

test('mitra can update their own product', function () {
    $produk = Produk::create([
        'basecamp_id' => $this->basecampA->id,
        'nama_produk' => 'Rental Tenda A',
        'kategori' => 'rental',
        'harga' => 35000,
        'stok' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->mitraA->user)->putJson(route('mitra.products.update', ['id' => $produk->id]), [
        'basecamp_id' => $this->basecampA->id,
        'nama_produk' => 'Rental Tenda A Updated',
        'kategori' => 'rental',
        'harga' => 45000,
        'stok' => 10,
        'is_active' => true,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('produks', [
        'id' => $produk->id,
        'nama_produk' => 'Rental Tenda A Updated',
        'harga' => 45000,
    ]);
});

test('mitra cannot update another mitras product', function () {
    $produkB = Produk::create([
        'basecamp_id' => $this->basecampB->id,
        'nama_produk' => 'Rental Tenda B',
        'kategori' => 'rental',
        'harga' => 40000,
        'stok' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->mitraA->user)->putJson(route('mitra.products.update', ['id' => $produkB->id]), [
        'basecamp_id' => $this->basecampA->id,
        'nama_produk' => 'Rental Tenda B Hacked',
        'kategori' => 'rental',
        'harga' => 10000,
        'stok' => 5,
    ]);

    $response->assertStatus(403);
});

test('mitra can delete their own product', function () {
    $produk = Produk::create([
        'basecamp_id' => $this->basecampA->id,
        'nama_produk' => 'Rental Tenda A',
        'kategori' => 'rental',
        'harga' => 35000,
        'stok' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->mitraA->user)->deleteJson(route('mitra.products.destroy', ['id' => $produk->id]));

    $response->assertStatus(200);
    $this->assertDatabaseMissing('produks', ['id' => $produk->id]);
});

test('mitra cannot delete another mitras product', function () {
    $produkB = Produk::create([
        'basecamp_id' => $this->basecampB->id,
        'nama_produk' => 'Rental Tenda B',
        'kategori' => 'rental',
        'harga' => 40000,
        'stok' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->mitraA->user)->deleteJson(route('mitra.products.destroy', ['id' => $produkB->id]));

    $response->assertStatus(403);
});

test('climbers can list active products and see details', function () {
    $climber = User::factory()->create(['role' => 'pendaki']);
    $climber->markEmailAsVerified();

    $activeProduk = Produk::create([
        'basecamp_id' => $this->basecampA->id,
        'nama_produk' => 'Active Product',
        'kategori' => 'rental',
        'harga' => 10000,
        'is_active' => true,
    ]);

    $inactiveProduk = Produk::create([
        'basecamp_id' => $this->basecampA->id,
        'nama_produk' => 'Inactive Product',
        'kategori' => 'rental',
        'harga' => 10000,
        'is_active' => false,
    ]);

    // Climber can list active products
    $response = $this->actingAs($climber)->getJson(route('products.index'));
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data.data');
    $response->assertJsonPath('data.data.0.id', $activeProduk->id);

    // Climber can show active product
    $response = $this->actingAs($climber)->getJson(route('products.show', ['id' => $activeProduk->id]));
    $response->assertStatus(200);

    // Climber cannot show inactive product
    $response = $this->actingAs($climber)->getJson(route('products.show', ['id' => $inactiveProduk->id]));
    $response->assertStatus(404);
});

test('climbers cannot write products', function () {
    $climber = User::factory()->create(['role' => 'pendaki']);
    $climber->markEmailAsVerified();

    $response = $this->actingAs($climber)->postJson(route('mitra.products.store'), [
        'basecamp_id' => $this->basecampA->id,
        'nama_produk' => 'Climber Product',
        'kategori' => 'rental',
        'harga' => 10000,
    ]);

    $response->assertStatus(403);
});

test('admin can see all products and filter by basecamp', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $produkA = Produk::create([
        'basecamp_id' => $this->basecampA->id,
        'nama_produk' => 'Tenda A',
        'kategori' => 'rental',
        'harga' => 10000,
        'is_active' => true,
    ]);

    $produkB = Produk::create([
        'basecamp_id' => $this->basecampB->id,
        'nama_produk' => 'Tenda B',
        'kategori' => 'rental',
        'harga' => 20000,
        'is_active' => false, // Inactive
    ]);

    // Admin list all products (should see both)
    $response = $this->actingAs($admin)->getJson(route('admin.products.index'));
    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data.data');

    // Admin filter by basecamp A
    $response = $this->actingAs($admin)->getJson(route('admin.products.index', ['basecamp_id' => $this->basecampA->id]));
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data.data');
    $response->assertJsonPath('data.data.0.id', $produkA->id);

    // Admin can view details of inactive product
    $response = $this->actingAs($admin)->getJson(route('admin.products.show', ['id' => $produkB->id]));
    $response->assertStatus(200);
    $response->assertJsonPath('data.id', $produkB->id);
});

test('non-admins cannot access admin product endpoints', function () {
    $mitra = $this->mitraA->user;
    $climber = User::factory()->create(['role' => 'pendaki']);
    $climber->markEmailAsVerified();

    $response = $this->actingAs($mitra)->getJson(route('admin.products.index'));
    $response->assertStatus(403);

    $response = $this->actingAs($climber)->getJson(route('admin.products.index'));
    $response->assertStatus(403);
});

test('admin can see products inside basecamp details', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $produk = Produk::create([
        'basecamp_id' => $this->basecampA->id,
        'nama_produk' => 'Tenda Basecamp A',
        'kategori' => 'rental',
        'harga' => 10000,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->getJson(route('admin.basecamp.show', ['id' => $this->basecampA->id]));
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data.produks');
    $response->assertJsonPath('data.produks.0.id', $produk->id);
});
