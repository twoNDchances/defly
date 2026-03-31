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
        Schema::create('policies_permissions', function (Blueprint $table) {
            $table->foreignUuid('policy')->constrained('policies')->cascadeOnDelete();
            $table->foreignUuid('permission')->constrained('permissions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies_permissions');
    }
};
