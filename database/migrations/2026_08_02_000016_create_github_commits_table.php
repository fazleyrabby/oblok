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
        Schema::create('github_commits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('github_integration_id')->constrained('github_integrations')->cascadeOnDelete();
            $table->string('sha');
            $table->text('message');
            $table->string('author_name');
            $table->string('author_email')->nullable();
            $table->timestamp('authored_at')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();

            $table->unique(['github_integration_id', 'sha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('github_commits');
    }
};
