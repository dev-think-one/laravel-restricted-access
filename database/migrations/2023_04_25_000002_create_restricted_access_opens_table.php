<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('restricted-access.tables.opens'), callback: function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('browser_fingerprint');
            $table->foreignId('link_id')
                ->nullable()
                ->constrained(config('restricted-access.tables.links'), 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->nullableMorphs('viewer');
            $table->dateTime('viewed_at')->nullable();
            $table->json('verification_result')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('restricted-access.tables.opens'));
    }
};
