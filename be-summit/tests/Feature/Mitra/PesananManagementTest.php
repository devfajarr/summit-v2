<?php

use App\Models\Basecamp;
use App\Models\DetailPesanan;
use App\Models\Gunung;
use App\Models\JalurPendakian;
use App\Models\Mitra;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    // Setup Mountain and Trail
    $this->gunung = Gunung::create([
        'nama_gunung' => 'Gunung Merbabu',
        'deskripsi' => 'Gunung Jawa Tengah.',
        'tinggi_mdpl' => 3142,
        'lokasi' => 'Boyolali, Jawa Tengah',
        'foto' => 'gunungs/merbabu.jpg',
        'status' => 'aktif',
    ]);

    $this->jalur = JalurPendakian::create([
        'gunung_id' => $this->gunung->id,
        'nama_jalur' => 'Jalur Selo',
        'deskripsi' => 'Jalur pendakian via Selo.',
        'titik_awal_mdpl' => '1400 MDPL',
        'titik_akhir_mdpl' => '3142 MDPL',
        'waktu_tempuh' => '6 Jam',
        'status' => 'open',
        'panjang_jalur' => '7.5 Km',
        'tingkat_kesulitan' => 'sedang',
    ]);

    // Setup Mitra A
    $userMitraA = User::factory()->create(['role' => 'mitra', 'name' => 'Mitra Ahmad']);
    $this->mitraA = Mitra::create([
        'user_id' => $userMitraA->id,
        'nama_pemilik' => 'Mitra Ahmad',
        'telepon' => '081200000001',
        'alamat' => 'Selo Boyolali',
        'nik' => '3201234567890001',
        'rekening_bank' => '1234567890',
        'nama_rekening' => 'Mitra Ahmad',
        'bank' => 'Bank BCA',
        'status' => 'aktif',
    ]);
    $this->basecampA = Basecamp::create([
        'mitra_id' => $this->mitraA->id,
        'jalur_id' => $this->jalur->id,
        'nama_basecamp' => 'Basecamp Selo Indah',
        'latitude' => '-7.412345',
        'longitude' => '110.432109',
        'jam_operasional' => '24 Jam',
    ]);
    $this->userMitraA = $userMitraA;

    // Setup Mitra B
    $userMitraB = User::factory()->create(['role' => 'mitra', 'name' => 'Mitra Bambang']);
    $this->mitraB = Mitra::create([
        'user_id' => $userMitraB->id,
        'nama_pemilik' => 'Mitra Bambang',
        'telepon' => '081200000002',
        'alamat' => 'Selo Kidul',
        'nik' => '3201234567890002',
        'rekening_bank' => '1234567891',
        'nama_rekening' => 'Mitra Bambang',
        'bank' => 'Bank Mandiri',
        'status' => 'aktif',
    ]);
    $this->basecampB = Basecamp::create([
        'mitra_id' => $this->mitraB->id,
        'jalur_id' => $this->jalur->id,
        'nama_basecamp' => 'Basecamp Selo Asri',
        'latitude' => '-7.412350',
        'longitude' => '110.432115',
        'jam_operasional' => '24 Jam',
    ]);
    $this->userMitraB = $userMitraB;

    // Setup Products
    $this->produkTicketA = Produk::create([
        'basecamp_id' => $this->basecampA->id,
        'nama_produk' => 'Tiket Simaksi Ahmad',
        'kategori' => 'ticket',
        'harga' => 25000.00,
        'is_active' => true,
    ]);

    $this->produkTicketB = Produk::create([
        'basecamp_id' => $this->basecampB->id,
        'nama_produk' => 'Tiket Simaksi Bambang',
        'kategori' => 'ticket',
        'harga' => 30000.00,
        'is_active' => true,
    ]);

    // Setup Hiker
    $this->hiker = User::factory()->create(['role' => 'pendaki']);

    // Setup Bookings
    // 1. Paid Booking for Basecamp A
    $this->pesananPaidA = Pesanan::create([
        'invoice' => 'INV/A/PAID',
        'user_id' => $this->hiker->id,
        'basecamp_id' => $this->basecampA->id,
        'jalur_id' => $this->jalur->id,
        'status' => 'paid',
        'subtotal' => 50000.00,
        'tanggal_booking' => '2026-08-01',
        'biaya_layanan_user' => 5000.00,
        'komisi_admin' => 5000.00,
        'pendapatan_mitra' => 45000.00,
        'total_bayar' => 55000.00,
    ]);
    $this->detailPaidA = DetailPesanan::create([
        'pesanan_id' => $this->pesananPaidA->id,
        'produk_id' => $this->produkTicketA->id,
        'qty' => 2,
        'harga' => 25000.00,
        'subtotal' => 50000.00,
        'status_operasional' => 'pending',
    ]);

    // 2. Pending Booking for Basecamp A
    $this->pesananPendingA = Pesanan::create([
        'invoice' => 'INV/A/PENDING',
        'user_id' => $this->hiker->id,
        'basecamp_id' => $this->basecampA->id,
        'jalur_id' => $this->jalur->id,
        'status' => 'pending',
        'subtotal' => 25000.00,
        'tanggal_booking' => '2026-08-02',
        'biaya_layanan_user' => 5000.00,
        'komisi_admin' => 2500.00,
        'pendapatan_mitra' => 22500.00,
        'total_bayar' => 30000.00,
    ]);

    // 3. Paid Booking for Basecamp B
    $this->pesananPaidB = Pesanan::create([
        'invoice' => 'INV/B/PAID',
        'user_id' => $this->hiker->id,
        'basecamp_id' => $this->basecampB->id,
        'jalur_id' => $this->jalur->id,
        'status' => 'paid',
        'subtotal' => 30000.00,
        'tanggal_booking' => '2026-08-01',
        'biaya_layanan_user' => 5000.00,
        'komisi_admin' => 3000.00,
        'pendapatan_mitra' => 27000.00,
        'total_bayar' => 35000.00,
    ]);
});

test('mitra can list their incoming orders and see revenue metrics', function () {
    $response = $this->actingAs($this->userMitraA)
        ->getJson(route('mitra.pesanan.index'));

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('meta.total_pendapatan_bersih', 45000) // Only the paid order for basecamp A
        ->assertJsonPath('meta.total_transaksi_paid', 1);

    $data = $response->json('data.data');
    expect($data)->toHaveCount(2); // INV/A/PAID and INV/A/PENDING

    // Ensure invoice INV/B/PAID is not visible to Mitra A
    $invoices = collect($data)->pluck('invoice');
    expect($invoices)->toContain('INV/A/PAID');
    expect($invoices)->toContain('INV/A/PENDING');
    expect($invoices)->not->toContain('INV/B/PAID');
});

test('mitra can view details of their own booking', function () {
    $response = $this->actingAs($this->userMitraA)
        ->getJson(route('mitra.pesanan.show', $this->pesananPaidA->id));

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.invoice', 'INV/A/PAID');
});

test('mitra is forbidden from viewing details of another basecamp booking', function () {
    $response = $this->actingAs($this->userMitraA)
        ->getJson(route('mitra.pesanan.show', $this->pesananPaidB->id));

    $response->assertStatus(403);
});

test('mitra can update operational status of their own booking items', function () {
    $payload = [
        'status_operasional' => 'ready',
    ];

    $response = $this->actingAs($this->userMitraA)
        ->patchJson(route('mitra.pesanan.update-item', [
            'pesananId' => $this->pesananPaidA->id,
            'itemId' => $this->detailPaidA->id,
        ]), $payload);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success');

    $this->detailPaidA->refresh();
    expect($this->detailPaidA->status_operasional)->toBe('ready');
});

test('mitra is forbidden from updating operational status of another basecamp booking items', function () {
    // Attempting to update Mitra B's item using Mitra A's token
    $detailB = DetailPesanan::create([
        'pesanan_id' => $this->pesananPaidB->id,
        'produk_id' => $this->produkTicketB->id,
        'qty' => 1,
        'harga' => 30000.00,
        'subtotal' => 30000.00,
        'status_operasional' => 'pending',
    ]);

    $payload = [
        'status_operasional' => 'ready',
    ];

    $response = $this->actingAs($this->userMitraA)
        ->patchJson(route('mitra.pesanan.update-item', [
            'pesananId' => $this->pesananPaidB->id,
            'itemId' => $detailB->id,
        ]), $payload);

    $response->assertStatus(403);
});
