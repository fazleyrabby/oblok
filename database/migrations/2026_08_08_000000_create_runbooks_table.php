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
        Schema::create('runbooks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('artisan');
            $table->json('config')->nullable();
            $table->string('trigger_type')->default('both'); // manual, automatic, both
            $table->boolean('enabled')->default(true);
            $table->integer('cooldown_minutes')->default(10);
            $table->integer('timeout_seconds')->default(30);
            $table->timestamp('last_executed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'enabled']);
            $table->index(['project_id', 'type']);
        });

        Schema::create('runbook_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('runbook_id')->constrained('runbooks')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('triggered_by_type')->default('manual'); // manual, service_failure, alert_rule
            $table->string('triggered_by_id')->nullable();
            $table->string('status')->default('pending'); // pending, running, successful, failed
            $table->text('output')->nullable();
            $table->integer('exit_code')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['runbook_id', 'created_at']);
            $table->index(['project_id', 'status']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreignUuid('runbook_id')
                ->nullable()
                ->after('is_flapping')
                ->constrained('runbooks')
                ->nullOnDelete();
        });

        Schema::table('alert_rules', function (Blueprint $table) {
            $table->foreignUuid('runbook_id')
                ->nullable()
                ->after('active_event_id')
                ->constrained('runbooks')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alert_rules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('runbook_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('runbook_id');
        });

        Schema::dropIfExists('runbook_runs');
        Schema::dropIfExists('runbooks');
    }
};
