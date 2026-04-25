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
        Schema::create('decisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique()->index();
            $table->enum('direction', ['request', 'response']);
            $table->enum('condition', ['<=', '<', '=', '>=', '>']);
            $table->float('score');
            $table->enum('action', ['allow', 'deny', 'rewrite_headers', 'rewrite_body', 'redirect', 'cancel', 'rewrite', 'save', 'erase_cookies', 'force_no_cache']);
            $table->json('configurations')->nullable();
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
        Schema::dropIfExists('decisions');
    }
};
