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
        Schema::create('meja', function (Blueprint $table) {
            $table->id('id_meja');
            $table->string('nomor_meja', 20)->unique();
            $table->string('qr_code_path', 255)->nullable();
            $table->unsignedInteger('kapasitas')->nullable();
            $table->enum('status', ['tersedia', 'terisi', 'tidak_aktif'])->default('tersedia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meja');
    }
};

