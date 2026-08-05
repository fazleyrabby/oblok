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
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('endpoint');
            $table->text('api_key')->nullable();
            $table->json('models');
            $table->integer('timeout')->default(60);
            $table->timestamps();
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignUuid('selected_provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
            $table->string('selected_model')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['selected_provider_id']);
            $table->dropColumn(['selected_provider_id', 'selected_model']);
        });

        Schema::dropIfExists('ai_providers');
    }
};
