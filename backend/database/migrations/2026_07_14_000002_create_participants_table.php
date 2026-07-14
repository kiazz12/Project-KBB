<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->integer('no');
            $table->string('nama');
            $table->string('jabatan')->nullable();
            $table->string('opd_institusi')->nullable();
            $table->string('role')->nullable();
            $table->date('tanggal_presensi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
