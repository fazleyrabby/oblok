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
        Schema::table('alert_events', function (Blueprint $table) {
            $table->string('state')->default('firing')->after('context');
            $table->timestamp('resolved_at')->nullable()->after('triggered_at');
            $table->string('fingerprint')->nullable()->after('state');

            $table->index(['project_id', 'state']);
            $table->index(['fingerprint', 'state']);
        });

        Schema::table('alert_rules', function (Blueprint $table) {
            $table->foreignUuid('active_event_id')
                ->nullable()
                ->after('cooldown_minutes')
                ->constrained('alert_events')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alert_rules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('active_event_id');
        });

        Schema::table('alert_events', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'state']);
            $table->dropIndex(['fingerprint', 'state']);
            $table->dropColumn(['state', 'resolved_at', 'fingerprint']);
        });
    }
};
