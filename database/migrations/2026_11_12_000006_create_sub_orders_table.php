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
        Schema::create('sub_orders', function (Blueprint $table) {
            $table->id('id_suborder');
            $table->unsignedBigInteger('id_order');
            $table->unsignedBigInteger('id_tenant');
            $table->string('kode_suborder', 60)->unique();
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->enum('status_pembayaran', [
                'menunggu_pembayaran',
                'sudah_dibayar',
                'dibatalkan',
            ])->default('menunggu_pembayaran');
            $table->enum('status_pesanan', [
                'menunggu',
                'diproses',
                'siap_disajikan',
                'selesai',
                'dibatalkan',
            ])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->timestamps();

            $table->unique(['id_suborder', 'id_order']);

            $table->foreign('id_order')
                ->references('id_order')
                ->on('orders')
                ->restrictOnDelete();

            $table->foreign('id_tenant')
                ->references('id_tenant')
                ->on('tenants')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_orders');
    }
};
