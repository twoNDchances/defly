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
        Schema::create('defenders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique()->index();
            $table->integer('proxy_port')->default(9948);
            $table->json('environment_variables');
            $table->enum('status', ['normal', 'abnormal'])->nullable();
            $table->json('details')->nullable();
            $table->enum('deployment_status', ['pending', 'deploying', 'failed', 'successful'])->nullable();
            $table->longText('deployment_details')->nullable();
            $table->longText('description')->nullable();
            $table->foreignUuid('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defenders');
    }
};
