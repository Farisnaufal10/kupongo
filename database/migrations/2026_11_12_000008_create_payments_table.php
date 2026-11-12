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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('id_payment');
            $table->unsignedBigInteger('id_order');
            $table->unsignedBigInteger('id_suborder')->nullable();
            $table->string('kode_payment', 60)->unique();
            $table->string('idempotency_key', 100)->unique()->nullable();
            $table->string('transaction_id', 100)->unique()->nullable();
            $table->enum('metode_pembayaran', ['qris', 'tunai']);
            $table->decimal('jumlah_bayar', 12, 2);
            $table->enum('status_pembayaran', [
                'pending',
                'berhasil',
                'gagal',
                'kedaluwarsa',
            ])->default('pending');
            $table->dateTime('waktu_pembayaran')->nullable();
            $table->json('payload_transaksi')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_order')
                ->references('id_order')
                ->on('orders')
                ->restrictOnDelete();

            $table->foreign(['id_suborder', 'id_order'])
                ->references(['id_suborder', 'id_order'])
                ->on('sub_orders')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
