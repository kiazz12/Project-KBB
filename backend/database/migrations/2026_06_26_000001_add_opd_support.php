<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opds', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('opd_id')->nullable()->after('role')->constrained('opds')->nullOnDelete();
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->foreignId('opd_id')->nullable()->after('user_id')->constrained('opds')->nullOnDelete();
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->enum('data_classification', ['public', 'internal', 'sensitive'])->default('public')->after('status');
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('permission');
            $table->timestamps();
            $table->unique(['role', 'permission']);
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('opd_id');
            $table->dropColumn('data_classification');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('opd_id');
        });

        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('opds');
    }
};
