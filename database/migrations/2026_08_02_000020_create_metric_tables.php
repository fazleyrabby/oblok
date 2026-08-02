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
        Schema::create('metric_samples', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('labels')->nullable();
            $table->float('value');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['project_id', 'name', 'recorded_at']);
        });

        Schema::create('metric_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('url');
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_scraped_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metric_targets');
        Schema::dropIfExists('metric_samples');
    }
};
