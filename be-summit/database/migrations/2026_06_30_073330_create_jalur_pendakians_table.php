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
        Schema::create('jalur_pendakians', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gunung_id');
            $table->string('nama_jalur');
            $table->text('deskripsi');
            $table->string('titik_awal_mdpl');
            $table->string('titik_akhir_mdpl');
            $table->string('waktu_tempuh');
            $table->enum('status', ['open', 'close'])->default('open');
            $table->string('panjang_jalur');
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit', 'ekstrem']);
            $table->timestamps();


            $table->foreign('gunung_id')->references('id')->on('gunungs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jalur_pendakians');
    }
};
