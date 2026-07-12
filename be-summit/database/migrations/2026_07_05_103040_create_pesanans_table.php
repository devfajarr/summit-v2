<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('basecamp_id')->constrained('basecamps')->cascadeOnDelete();
            $table->foreignId('jalur_id')->constrained('jalur_pendakians')->cascadeOnDelete();
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->decimal('subtotal', 15, 2);
            $table->date('tanggal_booking');
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('biaya_layanan_user', 15, 2)->default(0);
            $table->decimal('komisi_admin', 15, 2)->default(0);
            $table->decimal('pendapatan_mitra', 15, 2)->default(0);
            $table->decimal('total_bayar', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanans');
    }
};
