<?php

use App\Models\Basecamp;
use App\Models\DetailPesanan;
use App\Models\Gunung;
use App\Models\JalurPendakian;
use App\Models\Mitra;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\PesananAnggota;
use App\Models\Produk;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

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

    $this->produkRental = Produk::create([
        'basecamp_id' => $this->basecamp->id,
        'nama_produk' => 'Sewa Tenda',
        'kategori' => 'rental',
        'harga' => 35000.00,
        'is_active' => true,
    ]);
});

test('can create order with members, operational details, refunds, and payment gateway references', function () {
    // 1. Create Pesanan (using the refunded status enum value)
    $pesanan = Pesanan::create([
        'invoice' => 'INV-20260705-0001',
        'user_id' => $this->user->id,
        'basecamp_id' => $this->basecamp->id,
        'jalur_id' => $this->jalur->id,
        'status' => 'refunded',
        'subtotal' => 90000.00,
        'tanggal_booking' => '2026-07-10',
        'diskon' => 0.00,
        'biaya_layanan_user' => 5000.00,
        'komisi_admin' => 9000.00,
        'pendapatan_mitra' => 81000.00,
        'total_bayar' => 95000.00,
    ]);

    $this->assertDatabaseHas('pesanans', [
        'invoice' => 'INV-20260705-0001',
        'status' => 'refunded',
    ]);

    // 2. Add Hiker Members (Simaksi)
    $member1 = PesananAnggota::create([
        'pesanan_id' => $pesanan->id,
        'nama_anggota' => 'Hiker Satu',
        'nik_identitas' => '3201234567890005',
        'telepon' => '081234567888',
    ]);

    $this->assertDatabaseHas('pesanan_anggotas', [
        'pesanan_id' => $pesanan->id,
        'nama_anggota' => 'Hiker Satu',
    ]);

    // 3. Add Line Items with operational status
    $detail1 = DetailPesanan::create([
        'pesanan_id' => $pesanan->id,
        'produk_id' => $this->produkTicket->id,
        'qty' => 2,
        'harga' => 20000.00,
        'subtotal' => 40000.00,
        'status_operasional' => 'active',
        'kode_tiket' => 'TKT-CBD-001',
    ]);

    $detail2 = DetailPesanan::create([
        'pesanan_id' => $pesanan->id,
        'produk_id' => $this->produkRental->id,
        'qty' => 1,
        'harga' => 35000.00,
        'subtotal' => 35000.00,
        'status_operasional' => 'pending',
    ]);

    $this->assertDatabaseHas('detail_pesanans', [
        'id' => $detail1->id,
        'status_operasional' => 'active',
    ]);

    // 4. Add Payment (Xendit)
    $pembayaran = Pembayaran::create([
        'pesanan_id' => $pesanan->id,
        'metode' => 'qris',
        'provider' => 'QRIS',
        'xendit_invoice_id' => 'xendit-inv-abc123xyz',
        'checkout_url' => 'https://checkout.xendit.co/v2/invoice/abc123xyz',
        'status' => 'success',
        'biaya_gateway' => 750.00,
        'raw_response' => ['id' => 'xendit-inv-abc123xyz', 'status' => 'PAID', 'amount' => 95000],
    ]);

    $this->assertDatabaseHas('pembayarans', [
        'pesanan_id' => $pesanan->id,
        'status' => 'success',
    ]);

    // 5. Add Refund
    $refund = Refund::create([
        'pesanan_id' => $pesanan->id,
        'pembayaran_id' => $pembayaran->id,
        'xendit_refund_id' => 're_6092b3a1a63c8f',
        'tipe' => 'auto',
        'nominal' => 35000.00,
        'alasan' => 'Barang rental tenda rusak di lapangan',
        'status' => 'success',
        'raw_response' => ['id' => 're_6092b3a1a63c8f', 'status' => 'SUCCEEDED', 'amount' => 35000],
        'refunded_at' => now(),
    ]);

    $this->assertDatabaseHas('refunds', [
        'pesanan_id' => $pesanan->id,
        'xendit_refund_id' => 're_6092b3a1a63c8f',
        'nominal' => 35000.00,
    ]);

    // 6. Verify Relations
    expect($pesanan->user->id)->toBe($this->user->id);
    expect($pesanan->anggotas)->toHaveCount(1);
    expect($pesanan->details)->toHaveCount(2);
    expect($pesanan->pembayaran->id)->toBe($pembayaran->id);
    expect($pesanan->refunds)->toHaveCount(1);
    expect($pembayaran->refunds)->toHaveCount(1);

    expect($refund->pesanan->id)->toBe($pesanan->id);
    expect($refund->pembayaran->id)->toBe($pembayaran->id);
});

test('deleting an order cascades and deletes anggotas, details, pembayaran, and refunds', function () {
    $pesanan = Pesanan::create([
        'invoice' => 'INV-20260705-0002',
        'user_id' => $this->user->id,
        'basecamp_id' => $this->basecamp->id,
        'jalur_id' => $this->jalur->id,
        'status' => 'pending',
        'subtotal' => 20000.00,
        'tanggal_booking' => '2026-07-10',
        'total_bayar' => 25000.00,
    ]);

    $member = PesananAnggota::create([
        'pesanan_id' => $pesanan->id,
        'nama_anggota' => 'Hiker Tunggal',
        'nik_identitas' => '3201234567890007',
    ]);

    $detail = DetailPesanan::create([
        'pesanan_id' => $pesanan->id,
        'produk_id' => $this->produkTicket->id,
        'qty' => 1,
        'harga' => 20000.00,
        'subtotal' => 20000.00,
    ]);

    $pembayaran = Pembayaran::create([
        'pesanan_id' => $pesanan->id,
        'status' => 'pending',
    ]);

    $refund = Refund::create([
        'pesanan_id' => $pesanan->id,
        'pembayaran_id' => $pembayaran->id,
        'nominal' => 20000.00,
        'alasan' => 'Force cancel',
        'status' => 'pending',
    ]);

    // Act
    $pesanan->delete();

    // Assert
    $this->assertDatabaseMissing('pesanans', ['id' => $pesanan->id]);
    $this->assertDatabaseMissing('pesanan_anggotas', ['id' => $member->id]);
    $this->assertDatabaseMissing('detail_pesanans', ['id' => $detail->id]);
    $this->assertDatabaseMissing('pembayarans', ['id' => $pembayaran->id]);
    $this->assertDatabaseMissing('refunds', ['id' => $refund->id]);
});
