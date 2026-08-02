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
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('metric');
            $table->string('comparison');
            $table->integer('threshold')->nullable();
            $table->integer('consecutive_failures')->nullable()->default(1);
            $table->integer('window_minutes')->default(5);
            $table->string('severity')->default('warning');
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_evaluated_at')->nullable();
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('cooldown_minutes')->default(15);
            $table->timestamps();

            $table->index(['project_id', 'enabled']);
            $table->index(['metric', 'enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_rules');
    }
};
