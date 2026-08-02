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
        Schema::create('messaging_integrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('platform');
            $table->string('name');
            $table->text('config');
            $table->string('channel')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'platform']);
            $table->index('platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messaging_integrations');
    }
};
