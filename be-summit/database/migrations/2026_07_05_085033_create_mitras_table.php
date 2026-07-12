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
        Schema::create('mitras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_pemilik');
            $table->string('telepon');
            $table->text('alamat');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['aktif', 'suspend'])->default('aktif');
            $table->string('npwp')->nullable()->unique();
            $table->string('nik')->unique();
            $table->string('rekening_bank')->unique();
            $table->string('nama_rekening');
            $table->string('bank');
            $table->string('ewallet')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitras');
    }
};
