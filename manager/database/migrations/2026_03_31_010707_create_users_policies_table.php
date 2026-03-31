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
        Schema::create('users_policies', function (Blueprint $table) {
            $table->foreignUuid('user')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('policy')->constrained('policies')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_policies');
    }
};
