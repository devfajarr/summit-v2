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
        Schema::create('kuota_harian_tikets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_tiket_id')->constrained('produk_tikets')->cascadeOnDelete();
            $table->date('tanggal');
            $table->integer('kuota_total');
            $table->integer('kuota_tersisa');
            $table->timestamps();

            $table->unique(['produk_tiket_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuota_harian_tikets');
    }
};
