<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur presensi (daftar hadir/participants) sudah dihapus dari aplikasi.
 * Migrasi ini membersihkan tabel participants pada database yang sudah berjalan.
 *
 * PERHATIAN: up() menghapus tabel beserta seluruh data peserta secara permanen.
 * Pastikan backup database sudah dibuat sebelum menjalankan migrasi ini di produksi.
 * Form/submission lama yang pernah dipakai untuk presensi sengaja tidak dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('participants');
    }

    public function down(): void
    {
        // Tidak dibuat ulang: fitur presensi sudah tidak ada di aplikasi.
    }
};
