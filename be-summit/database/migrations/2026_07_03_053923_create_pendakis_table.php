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
        Schema::create('pendakis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('nama_lengkap');
            $table->enum('jenis_identitas', ['ktp', 'paspor', 'sim', 'lainnya'])->default('ktp');
            $table->string('nomor_identitas')->unique();
            $table->string('foto_identitas');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['l', 'p']);
            $table->text('alamat');
            $table->string('telepon');
            $table->string('nama_kontak_darurat');
            $table->string('telepon_darurat');
            $table->string('hubungan_darurat');
            $table->enum('status_verifikasi', ['belum_mengirimkan', 'pending', 'disetujui', 'ditolak'])->default('belum_mengirimkan');
            $table->text('alasan_penolakan')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendakis');
    }
};
