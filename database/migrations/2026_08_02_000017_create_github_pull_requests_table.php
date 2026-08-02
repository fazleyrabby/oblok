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
        Schema::create('github_pull_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('github_integration_id')->constrained('github_integrations')->cascadeOnDelete();
            $table->integer('number');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('state');
            $table->string('author_name');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('merged_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();

            $table->unique(['github_integration_id', 'number']);
            $table->index(['github_integration_id', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('github_pull_requests');
    }
};
