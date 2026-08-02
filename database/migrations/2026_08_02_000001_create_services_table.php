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
        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('http');
            $table->string('target');
            $table->integer('check_interval')->default(60);
            $table->integer('timeout')->default(5);
            $table->integer('expected_status_code')->default(200);
            $table->string('status')->default('unknown');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
        });

        Schema::create('health_check_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('status');
            $table->integer('status_code')->nullable();
            $table->integer('response_time_ms');
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['service_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_check_results');
        Schema::dropIfExists('services');
    }
};
