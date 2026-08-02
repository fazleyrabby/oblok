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
        Schema::create('alert_rule_channel', function (Blueprint $table) {
            $table->foreignUuid('alert_rule_id')->constrained('alert_rules')->cascadeOnDelete();
            $table->foreignUuid('notification_channel_id')->constrained('notification_channels')->cascadeOnDelete();
            $table->json('recipient_filter')->nullable();
            $table->timestamps();

            $table->primary(['alert_rule_id', 'notification_channel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_rule_channel');
    }
};
