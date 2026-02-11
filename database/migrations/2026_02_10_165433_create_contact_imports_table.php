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
        Schema::create('contact_imports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('queue_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('total_processed')->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->unsignedInteger('duplicates')->default(0);
            $table->string('file');
            $table->enum('state', ['PENDING', 'RUNNING', 'DONE', 'FAILED'])->default('PENDING');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_imports');
    }
};
