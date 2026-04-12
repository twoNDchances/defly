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
        Schema::create('targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique()->index();
            $table->integer('phase');
            $table->enum('type', ['getter', 'full', 'header', 'meta', 'query', 'body', 'file']);
            $table->enum('datatype', ['array', 'number', 'string']);
            $table->longText('description')->nullable();
            $table->foreignUuid('pattern_id')->nullable()->index()->constrained('patterns')->nullOnDelete();
            $table->foreignUuid('wordlist_id')->nullable()->index()->constrained('wordlists')->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
