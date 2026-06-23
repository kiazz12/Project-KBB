<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('admin')->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'opd')) {
                $table->string('opd')->nullable()->after('nip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['role', 'nip', 'opd'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
