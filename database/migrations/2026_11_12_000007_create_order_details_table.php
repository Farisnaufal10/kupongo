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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id('id_detail');
            $table->unsignedBigInteger('id_suborder');
            $table->unsignedBigInteger('id_menu')->nullable();
            $table->string('nama_menu', 150);
            $table->decimal('harga_satuan', 12, 2);
            $table->unsignedInteger('jumlah');
            $table->decimal('subtotal', 12, 2);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_suborder')
                ->references('id_suborder')
                ->on('sub_orders')
                ->restrictOnDelete();

            $table->foreign('id_menu')
                ->references('id_menu')
                ->on('menu')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
