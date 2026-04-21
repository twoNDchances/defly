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
        Schema::create('rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique()->index();
            $table->integer('phase');
            $table->foreignUuid('target_id')->nullable()->index()->constrained('targets')->nullOnDelete();
            $table->string('comparator');
            $table->boolean('is_inversed')->default(false);
            $table->json('configurations')->nullable();
            $table->foreignUuid('wordlist_id')->nullable()->index()->constrained('wordlists')->nullOnDelete();
            $table->longText('description')->nullable();
            $table->foreignUuid('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
