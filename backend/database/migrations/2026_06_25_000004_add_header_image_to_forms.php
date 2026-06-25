<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            if (! Schema::hasColumn('forms', 'header_image')) {
                $table->string('header_image')->nullable()->after('show_kbb_logo');
            }
            if (! Schema::hasColumn('forms', 'theme_color')) {
                $table->string('theme_color', 7)->nullable()->after('header_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $columns = ['header_image', 'theme_color'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('forms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
