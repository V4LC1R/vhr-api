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
        Schema::create('core.password_reset_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->text('token')->unique();

             $table->enum('status', ['pending', 'used'])->default('pending');

            $table->foreignUuid('userId')
                ->constrained('core.users')
                ->cascadeOnDelete();

            $table->string('ipAddress', 45)->nullable();
            $table->string('userAgent')->nullable();

            $table->timestamp('expiresAt');
            $table->timestamp('requestedAt');
            $table->timestamp('usedAt')->nullable(true);

            $table->index(['token', 'userId']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.password_reset_tokens');
    }
};
