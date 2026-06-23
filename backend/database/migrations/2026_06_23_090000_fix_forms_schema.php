<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            if (!Schema::hasColumn('forms', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('settings');
            }
            if (!Schema::hasColumn('forms', 'collect_ip')) {
                $table->boolean('collect_ip')->default(true)->after('max_submissions');
            }
            if (!Schema::hasColumn('forms', 'show_kbb_logo')) {
                $table->boolean('show_kbb_logo')->default(true)->after('collect_ip');
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
            $columns = ['starts_at', 'collect_ip', 'show_kbb_logo', 'limit_one_response', 'confirmation_type'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('forms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};