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
        Schema::create('targets_engines', function (Blueprint $table) {
            $table->foreignUuid('target')->constrained('targets')->cascadeOnDelete();
            $table->foreignUuid('engine')->constrained('engines')->cascadeOnDelete();
            $table->unsignedBigInteger('order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('targets_engines');
    }
};
