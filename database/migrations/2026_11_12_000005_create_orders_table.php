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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('id_order');
            $table->string('kode_order', 50)->unique();
            $table->unsignedBigInteger('id_meja');
            $table->string('nama_pembeli', 100)->nullable();
            $table->string('nomor_hp_pembeli', 20)->nullable();
            $table->enum('metode_pembayaran', ['qris', 'tunai']);
            $table->decimal('total_harga', 12, 2)->default(0.00);
            $table->enum('status_pembayaran', [
                'menunggu_pembayaran',
                'sebagian_dibayar',
                'sudah_dibayar',
                'dibatalkan',
                'kedaluwarsa',
            ])->default('menunggu_pembayaran');
            $table->dateTime('waktu_pesan');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_meja')
                ->references('id_meja')
                ->on('meja')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

