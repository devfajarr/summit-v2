<?php

use App\Models\Basecamp;
use App\Models\Gunung;
use App\Models\JalurPendakian;
use App\Models\KuotaHarianTiket;
use App\Models\Mitra;
use App\Models\Produk;
use App\Models\ProdukTiket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'pendaki']);

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

    $this->produkTicket = Produk::create([
        'basecamp_id' => $this->basecamp->id,
        'nama_produk' => 'Tiket Simaksi Cibodas',
        'kategori' => 'ticket',
        'harga' => 20000.00,
        'is_active' => true,
    ]);

    $this->produkTiketDetail = ProdukTiket::create([
        'produk_id' => $this->produkTicket->id,
        'jalur_id' => $this->jalur->id,
        'jam_buka' => '07:00:00',
        'jam_tutup' => '17:00:00',
    ]);

    $this->produkRental = Produk::create([
        'basecamp_id' => $this->basecamp->id,
        'nama_produk' => 'Sewa Tenda',
        'kategori' => 'rental',
        'harga' => 35000.00,
        'stok' => 10,
        'is_active' => true,
    ]);
});

test('climber can create a booking order successfully', function () {
    $payload = [
        'basecamp_id' => $this->basecamp->id,
        'jalur_id' => $this->jalur->id,
        'tanggal_booking' => '2026-08-10',
        'anggotas' => [
            [
                'nama_anggota' => 'Hiker Satu',
                'nik_identitas' => '3201234567890005',
                'telepon' => '081234567888',
                'telepon_darurat' => '081234567889',
                'hubungan_darurat' => 'Teman',
            ],
        ],
        'items' => [
            [
                'produk_id' => $this->produkTicket->id,
                'qty' => 2,
            ],
            [
                'produk_id' => $this->produkRental->id,
                'qty' => 1,
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('pesanan.store'), $payload);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'success')
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'id',
                'invoice',
                'user_id',
                'status',
                'subtotal',
                'total_bayar',
                'anggotas',
                'details',
                'pembayaran',
            ],
        ]);

    // Assert Database
    $this->assertDatabaseHas('pesanans', [
        'user_id' => $this->user->id,
        'basecamp_id' => $this->basecamp->id,
        'subtotal' => 75000.00, // (20000 * 2) + (35000 * 1)
        'total_bayar' => 80000.00, // 75000 + 5000 biaya layanan
    ]);

    // Assert stock is decremented
    $this->produkRental->refresh();
    expect($this->produkRental->stok)->toBe(9);

    // Assert quota is decremented
    $kuota = KuotaHarianTiket::where('produk_tiket_id', $this->produkTiketDetail->id)
        ->where('tanggal', Carbon::parse('2026-08-10')->startOfDay())
        ->first();
    expect($kuota->kuota_tersisa)->toBe(98); // 100 - 2
});

test('booking fails if rental product stock is insufficient', function () {
    // Set stock to 0
    $this->produkRental->update(['stok' => 0]);

    $payload = [
        'basecamp_id' => $this->basecamp->id,
        'jalur_id' => $this->jalur->id,
        'tanggal_booking' => '2026-08-10',
        'anggotas' => [
            [
                'nama_anggota' => 'Hiker Satu',
                'nik_identitas' => '3201234567890005',
            ],
        ],
        'items' => [
            [
                'produk_id' => $this->produkRental->id,
                'qty' => 1,
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('pesanan.store'), $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('items');
});

test('booking fails if ticket daily quota is insufficient', function () {
    // Create daily quota with only 1 slot left
    KuotaHarianTiket::create([
        'produk_tiket_id' => $this->produkTiketDetail->id,
        'tanggal' => '2026-08-10',
        'kuota_total' => 100,
        'kuota_tersisa' => 1,
    ]);

    $payload = [
        'basecamp_id' => $this->basecamp->id,
        'jalur_id' => $this->jalur->id,
        'tanggal_booking' => '2026-08-10',
        'anggotas' => [
            [
                'nama_anggota' => 'Hiker Satu',
                'nik_identitas' => '3201234567890005',
            ],
        ],
        'items' => [
            [
                'produk_id' => $this->produkTicket->id,
                'qty' => 2,
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('pesanan.store'), $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('items');
});

test('booking fails if double submitting (atomic lock works)', function () {
    // Manually acquire lock for user
    $lockKey = 'create_pesanan_user_'.$this->user->id;
    $lock = Cache::lock($lockKey, 10);
    $lock->acquire();

    $payload = [
        'basecamp_id' => $this->basecamp->id,
        'jalur_id' => $this->jalur->id,
        'tanggal_booking' => '2026-08-10',
        'anggotas' => [
            [
                'nama_anggota' => 'Hiker Satu',
                'nik_identitas' => '3201234567890005',
            ],
        ],
        'items' => [
            [
                'produk_id' => $this->produkTicket->id,
                'qty' => 1,
            ],
        ],
    ];

    // The request should block and eventually fail with LockTimeoutException (which returns transaction error)
    $response = $this->actingAs($this->user)
        ->postJson(route('pesanan.store'), $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('transaction');

    $lock->release();
});
