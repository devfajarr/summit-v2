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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanans')->cascadeOnDelete();
            $table->enum('metode', ['transfer', 'ewallet', 'qris', 'manual'])->nullable();
            $table->string('provider')->nullable();
            $table->string('xendit_invoice_id')->nullable()->unique();
            $table->text('checkout_url')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->decimal('biaya_gateway', 15, 2)->default(0);
            $table->json('raw_response')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
