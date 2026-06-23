<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            if (!Schema::hasColumn('forms', 'confirmation_message')) {
                $table->text('confirmation_message')->nullable()->after('settings');
            }
            if (!Schema::hasColumn('forms', 'limit_one_response')) {
                $table->boolean('limit_one_response')->default(false)->after('confirmation_message');
            }
            if (!Schema::hasColumn('forms', 'confirmation_type')) {
                $table->string('confirmation_type', 20)->default('message')->after('limit_one_response');
            }
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $columns = ['confirmation_message', 'limit_one_response', 'confirmation_type'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('forms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
