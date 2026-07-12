<?php

use App\Http\Controllers\Admin\BasecampController as AdminBasecampController;
use App\Http\Controllers\Admin\GunungController as AdminGunungController;
use App\Http\Controllers\Admin\JalurController as AdminJalurController;
use App\Http\Controllers\Admin\KycController as AdminKycController;
use App\Http\Controllers\Admin\MitraController as AdminMitraController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Mitra\PesananController as MitraPesananController;
use App\Http\Controllers\Mitra\ProductController as MitraProductController;
use App\Http\Controllers\Pendaki\GunungController as PendakiGunungController;
use App\Http\Controllers\Pendaki\KycController as PendakiKycController;
use App\Http\Controllers\Pendaki\PesananController as PendakiPesananController;
use App\Http\Controllers\Pendaki\ProductController as PendakiProductController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api')->name('login');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:api')->name('resend-otp');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // KYC endpoints for Climber (Pendaki)
    Route::post('/kyc/submit', [PendakiKycController::class, 'submit'])->name('kyc.submit');
    Route::get('/kyc/status', [PendakiKycController::class, 'status'])->name('kyc.status');

    // Gunung & Jalur endpoints for Climber (Pendaki) - Read-only
    Route::get('/gunung', [PendakiGunungController::class, 'index'])->name('gunung.index');
    Route::get('/gunung/{id}', [PendakiGunungController::class, 'show'])->name('gunung.show');

    // Product endpoints for Climber (Pendaki) - Read-only
    Route::get('/products', [PendakiProductController::class, 'index'])->name('products.index');
    Route::get('/products/{id}', [PendakiProductController::class, 'show'])->name('products.show');

    // Booking transaction endpoint for Climber (Pendaki)
    Route::post('/pesanan', [PendakiPesananController::class, 'store'])->name('pesanan.store');

    // Admin endpoints (guarded by role:admin)
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // KYC management
        Route::get('/kyc', [AdminKycController::class, 'index'])->name('admin.kyc.index');
        Route::get('/kyc/{id}', [AdminKycController::class, 'show'])->name('admin.kyc.show');
        Route::get('/kyc/{id}/download-document', [AdminKycController::class, 'downloadDocument'])->name('admin.kyc.download');
        Route::post('/kyc/{id}/verify', [AdminKycController::class, 'verify'])->name('admin.kyc.verify');

        // Gunung Management
        Route::post('/gunung', [AdminGunungController::class, 'store'])->name('admin.gunung.store');
        Route::match(['PUT', 'PATCH'], '/gunung/{id}', [AdminGunungController::class, 'update'])->name('admin.gunung.update');
        Route::delete('/gunung/{id}', [AdminGunungController::class, 'destroy'])->name('admin.gunung.destroy');

        // Jalur Pendakian Management
        Route::post('/jalur', [AdminJalurController::class, 'store'])->name('admin.jalur.store');
        Route::match(['PUT', 'PATCH'], '/jalur/{id}', [AdminJalurController::class, 'update'])->name('admin.jalur.update');
        Route::delete('/jalur/{id}', [AdminJalurController::class, 'destroy'])->name('admin.jalur.destroy');

        // Mitra Management
        Route::get('/mitra', [AdminMitraController::class, 'index'])->name('admin.mitra.index');
        Route::post('/mitra', [AdminMitraController::class, 'store'])->name('admin.mitra.store');
        Route::get('/mitra/{id}', [AdminMitraController::class, 'show'])->name('admin.mitra.show');
        Route::match(['PUT', 'PATCH'], '/mitra/{id}', [AdminMitraController::class, 'update'])->name('admin.mitra.update');
        Route::delete('/mitra/{id}', [AdminMitraController::class, 'destroy'])->name('admin.mitra.destroy');

        // Basecamp Management
        Route::get('/basecamp', [AdminBasecampController::class, 'index'])->name('admin.basecamp.index');
        Route::post('/basecamp', [AdminBasecampController::class, 'store'])->name('admin.basecamp.store');
        Route::get('/basecamp/{id}', [AdminBasecampController::class, 'show'])->name('admin.basecamp.show');
        Route::match(['PUT', 'PATCH'], '/basecamp/{id}', [AdminBasecampController::class, 'update'])->name('admin.basecamp.update');
        Route::delete('/basecamp/{id}', [AdminBasecampController::class, 'destroy'])->name('admin.basecamp.destroy');

        // Product Monitoring
        Route::get('/products', [AdminProductController::class, 'index'])->name('admin.products.index');
        Route::get('/products/{id}', [AdminProductController::class, 'show'])->name('admin.products.show');
    });

    // Mitra endpoints (guarded by role:mitra)
    Route::middleware(['role:mitra'])->prefix('mitra')->group(function () {
        Route::get('/products', [MitraProductController::class, 'index'])->name('mitra.products.index');
        Route::post('/products', [MitraProductController::class, 'store'])->name('mitra.products.store');
        Route::get('/products/{id}', [MitraProductController::class, 'show'])->name('mitra.products.show');
        Route::match(['PUT', 'PATCH'], '/products/{id}', [MitraProductController::class, 'update'])->name('mitra.products.update');
        Route::delete('/products/{id}', [MitraProductController::class, 'destroy'])->name('mitra.products.destroy');

        // Order Management for Mitra
        Route::get('/pesanan', [MitraPesananController::class, 'index'])->name('mitra.pesanan.index');
        Route::get('/pesanan/{id}', [MitraPesananController::class, 'show'])->name('mitra.pesanan.show');
        Route::patch('/pesanan/{pesananId}/items/{itemId}', [MitraPesananController::class, 'updateItemStatus'])->name('mitra.pesanan.update-item');
    });
});
