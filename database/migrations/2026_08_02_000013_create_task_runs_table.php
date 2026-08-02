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
        Schema::create('task_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('scheduled_task_id')->constrained('scheduled_tasks')->cascadeOnDelete();
            $table->string('status')->default('running');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->integer('exit_code')->nullable();
            $table->text('output')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['scheduled_task_id', 'started_at']);
            $table->index(['scheduled_task_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_runs');
    }
};
