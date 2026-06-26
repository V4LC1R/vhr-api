<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('core.users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('password');

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('core.user_companies', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->foreignUuid('companyId')
                ->constrained('core.companies')
                ->cascadeOnDelete();

            $table->foreignUuid('userId')
                ->constrained('core.users')
                ->cascadeOnDelete();

            $table->foreignUuid('personId')
                ->nullable()
                ->constrained('core.persons')
                ->nullOnDelete();

            $table->unique([
                'companyId',
                'userId'
            ]);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core.users');
        Schema::dropIfExists('core.user_companies');
    }
};
