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
        Schema::create('produk_opentrips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->date('tanggal_berangkat');
            $table->date('tanggal_pulang');
            $table->string('meeting_point');
            $table->integer('minimal_peserta')->default(1);
            $table->integer('maksimal_peserta');
            $table->integer('sisa_kursi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_opentrips');
    }
};
