<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('submission_data')) {
            return;
        }

        Schema::create('submission_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('form_submissions')->cascadeOnDelete();
            $table->foreignId('form_field_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_data');
    }
};
