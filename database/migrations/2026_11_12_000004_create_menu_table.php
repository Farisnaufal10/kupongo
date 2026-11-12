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
        Schema::create('menu', function (Blueprint $table) {
            $table->id('id_menu');
            $table->unsignedBigInteger('id_tenant');
            $table->string('nama_menu', 150);
            $table->string('kategori', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 12, 2);
            $table->string('foto', 255)->nullable();
            $table->boolean('is_tersedia')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

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
        Schema::dropIfExists('menu');
    }
};
