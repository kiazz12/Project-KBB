<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * REA D-12: indeks performa untuk query yang terbukti lambat pada volume data
 * besar (analisis REA — lihat docs/REA-TEMUAN.md).
 *
 * Semua penambahan indeks dibuat idempoten lewat hasIndex() agar aman
 * dijalankan pada database yang sudah punya indeks serupa.
 */
return new class extends Migration
{
    private function addIndex(Blueprint $table, array $columns, string $name): void
    {
        if (! Schema::hasIndex($table->getTable(), $columns)) {
            $table->index($columns, $name);
        }
    }

    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            // Listing submission per form + pagination urut submitted_at.
            $this->addIndex($table, ['form_id', 'submitted_at'], 'form_submissions_form_id_submitted_at_index');
            // Cek limit_one_response (submission per user) & riwayat user.
            $this->addIndex($table, ['user_id'], 'form_submissions_user_id_index');
        });

        Schema::table('submission_data', function (Blueprint $table) {
            // Eager-load data per submission (N+1 pada daftar/detail).
            $this->addIndex($table, ['submission_id'], 'submission_data_submission_id_index');
            // Analitik per field (GROUP BY value WHERE form_field_id).
            $this->addIndex($table, ['form_field_id'], 'submission_data_form_field_id_index');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            // Filter halaman audit per user dan per jenis aksi.
            $this->addIndex($table, ['user_id', 'created_at'], 'audit_logs_user_id_created_at_index');
            $this->addIndex($table, ['action'], 'audit_logs_action_index');
            // Polymorphic audit (audit_logs() di model).
            $this->addIndex($table, ['auditable_type', 'auditable_id'], 'audit_logs_auditable_type_auditable_id_index');
        });

        Schema::table('user_sessions', function (Blueprint $table) {
            // Hitung sesi aktif user (WHERE user_id AND logged_out_at IS NULL).
            $this->addIndex($table, ['user_id', 'logged_out_at'], 'user_sessions_user_id_logged_out_at_index');
        });

        Schema::table('participants', function (Blueprint $table) {
            // Pencarian peserta LIKE '%...%' pada nama.
            if (Schema::hasColumn('participants', 'nama')
                && ! Schema::hasIndex('participants', ['nama'])) {
                $table->index('nama', 'participants_nama_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropIndex('form_submissions_form_id_submitted_at_index');
            $table->dropIndex('form_submissions_user_id_index');
        });

        Schema::table('submission_data', function (Blueprint $table) {
            $table->dropIndex('submission_data_submission_id_index');
            $table->dropIndex('submission_data_form_field_id_index');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_user_id_created_at_index');
            $table->dropIndex('audit_logs_action_index');
            $table->dropIndex('audit_logs_auditable_type_auditable_id_index');
        });

        Schema::table('user_sessions', function (Blueprint $table) {
            $table->dropIndex('user_sessions_user_id_logged_out_at_index');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex('participants_nama_index');
        });
    }
};
