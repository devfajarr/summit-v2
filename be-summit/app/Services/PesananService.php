<?php

namespace App\Services;

use App\Models\KuotaHarianTiket;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PesananService
{
    /**
     * Create a new booking transaction.
     *
     * @throws ValidationException
     */
    public function createPesanan(User $user, array $data): Pesanan
    {
        // 1. Prevent double submit using atomic lock on user ID
        $lockKey = 'create_pesanan_user_'.$user->id;
        $lock = Cache::lock($lockKey, 10); // lock for 10 seconds

        return $lock->block(5, function () use ($user, $data) {
            return DB::transaction(function () use ($user, $data) {
                $basecampId = $data['basecamp_id'];
                $jalurId = $data['jalur_id'];
                $tanggalBooking = $data['tanggal_booking'];

                // Sort items by product_id to prevent deadlocks
                $items = collect($data['items'])->sortBy('produk_id')->values()->all();

                $subtotal = 0.00;
                $detailsData = [];

                foreach ($items as $item) {
                    // Fetch product with pessimistic locking
                    $produk = Produk::where('id', $item['produk_id'])
                        ->where('basecamp_id', $basecampId)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if (! $produk) {
                        throw ValidationException::withMessages([
                            'items' => ["Produk dengan ID {$item['produk_id']} tidak aktif atau tidak ditemukan di basecamp ini."],
                        ]);
                    }

                    $qty = $item['qty'];
                    $harga = (float) $produk->harga;
                    $itemSubtotal = $harga * $qty;
                    $subtotal += $itemSubtotal;

                    // Handle product specific logic (ticket daily quota vs normal stock)
                    if ($produk->kategori === 'ticket') {
                        $produkTiket = $produk->tiket;
                        if (! $produkTiket || $produkTiket->jalur_id !== $jalurId) {
                            throw ValidationException::withMessages([
                                'items' => ["Produk tiket {$produk->nama_produk} tidak sesuai dengan jalur pendakian yang dipilih."],
                            ]);
                        }

                        // Fetch/Create Daily Quota with lock
                        $tanggalCarbon = Carbon::parse($tanggalBooking)->startOfDay();
                        $kuota = KuotaHarianTiket::firstOrCreate(
                            [
                                'produk_tiket_id' => $produkTiket->id,
                                'tanggal' => $tanggalCarbon,
                            ],
                            [
                                'kuota_total' => 100,
                                'kuota_tersisa' => 100,
                            ]
                        );

                        $kuota = KuotaHarianTiket::where('id', $kuota->id)
                            ->lockForUpdate()
                            ->first();

                        if ($kuota->kuota_tersisa < $qty) {
                            throw ValidationException::withMessages([
                                'items' => ["Kuota tiket {$produk->nama_produk} untuk tanggal {$tanggalBooking} tidak mencukupi (Tersisa: {$kuota->kuota_tersisa})."],
                            ]);
                        }

                        // Decrement quota
                        $kuota->decrement('kuota_tersisa', $qty);

                        // ticket code generator helper
                        $kodeTiket = 'TKT-'.strtoupper(Str::random(8));
                    } else {
                        // For rentals/merchandise with stock limit
                        if ($produk->stok !== null) {
                            if ($produk->stok < $qty) {
                                throw ValidationException::withMessages([
                                    'items' => ["Stok produk {$produk->nama_produk} tidak mencukupi (Tersisa: {$produk->stok})."],
                                ]);
                            }

                            // Decrement stock
                            $produk->decrement('stok', $qty);
                        }
                        $kodeTiket = null;
                    }

                    $detailsData[] = [
                        'produk_id' => $produk->id,
                        'qty' => $qty,
                        'harga' => $harga,
                        'subtotal' => $itemSubtotal,
                        'status_operasional' => 'pending',
                        'kode_tiket' => $kodeTiket,
                    ];
                }

                // Calculate financial metrics
                $biayaLayananUser = 5000.00; // Flat platform service fee
                $komisiAdmin = $subtotal * 0.10; // 10% admin fee
                $pendapatanMitra = $subtotal - $komisiAdmin;
                $totalBayar = $subtotal + $biayaLayananUser;

                // Generate unique invoice number
                $invoice = 'INV/'.date('Ymd').'/'.strtoupper(Str::random(6));

                // Create Order
                $pesanan = Pesanan::create([
                    'invoice' => $invoice,
                    'user_id' => $user->id,
                    'basecamp_id' => $basecampId,
                    'jalur_id' => $jalurId,
                    'status' => 'pending',
                    'subtotal' => $subtotal,
                    'tanggal_booking' => $tanggalBooking,
                    'diskon' => 0.00,
                    'biaya_layanan_user' => $biayaLayananUser,
                    'komisi_admin' => $komisiAdmin,
                    'pendapatan_mitra' => $pendapatanMitra,
                    'total_bayar' => $totalBayar,
                ]);

                // Save details
                foreach ($detailsData as $detail) {
                    $pesanan->details()->create($detail);
                }

                // Save members
                foreach ($data['anggotas'] as $anggota) {
                    $pesanan->anggotas()->create([
                        'nama_anggota' => $anggota['nama_anggota'],
                        'nik_identitas' => $anggota['nik_identitas'],
                        'telepon' => $anggota['telepon'] ?? null,
                        'telepon_darurat' => $anggota['telepon_darurat'] ?? null,
                        'hubungan_darurat' => $anggota['hubungan_darurat'] ?? null,
                    ]);
                }

                // Create initial Pembayaran record
                $pesanan->pembayaran()->create([
                    'status' => 'pending',
                ]);

                // Load relationships
                $pesanan->load(['user', 'basecamp', 'jalur', 'anggotas', 'details.produk', 'pembayaran']);

                return $pesanan;
            });
        });
    }
}
