<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela: core.users
        Schema::create('core.users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('password');

            // Enum UserStatus (active, inactive)
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Chave Estrangeira apontando para core.persons
            $table->foreignUuid('personId')
                  ->constrained('core.persons')
                  ->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core.users');
    }
};
