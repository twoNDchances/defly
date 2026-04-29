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
        Schema::create('defenders_principles', function (Blueprint $table) {
            $table->foreignUuid('defender')->constrained('defenders')->cascadeOnDelete();
            $table->foreignUuid('principle')->constrained('principles')->cascadeOnDelete();
            $table->unsignedBigInteger('order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defenders_principles');
    }
};
