<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('claimant_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('requested')->index();
            $table->text('claim_note');
            $table->text('owner_note')->nullable();
            $table->string('meeting_location')->nullable();
            $table->timestamp('meeting_at')->nullable();
            $table->timestamp('owner_confirmed_at')->nullable();
            $table->timestamp('claimant_confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('handover_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('handover_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handover_events');
        Schema::dropIfExists('handovers');
    }
};
