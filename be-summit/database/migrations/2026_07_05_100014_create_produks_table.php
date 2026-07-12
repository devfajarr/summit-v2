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
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('basecamp_id')->constrained('basecamps')->cascadeOnDelete();
            $table->string('nama_produk');
            $table->enum('kategori', [
                'ticket', 'rental', 'opentrip', 'guide', 'porter',
                'transport', 'parkir', 'merchandise', 'kuliner',
            ]);
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 12, 2);
            $table->integer('stok')->nullable();
            $table->string('satuan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('gambar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
