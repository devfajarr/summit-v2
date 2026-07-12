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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanans')->cascadeOnDelete();
            $table->foreignId('pembayaran_id')->constrained('pembayarans')->cascadeOnDelete();
            $table->string('xendit_refund_id')->nullable()->unique();
            $table->enum('tipe', ['auto', 'manual'])->default('auto');
            $table->decimal('nominal', 15, 2);
            $table->text('alasan');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('bank_tujuan')->nullable();
            $table->string('rekening_tujuan')->nullable();
            $table->string('nama_tujuan')->nullable();
            $table->string('bukti_transfer')->nullable();
            $table->json('raw_response')->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
