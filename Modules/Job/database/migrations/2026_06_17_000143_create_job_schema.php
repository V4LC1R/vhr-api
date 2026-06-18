<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Garante que o schema job existe antes de criar as tabelas
        DB::statement('CREATE SCHEMA IF NOT EXISTS job');

        // 1. Tabela: job.workloads
        Schema::create('job.workloads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('companyId');
            $table->string('description');
            $table->integer('monthly_hours');
            $table->integer('weekly_hours');
            $table->time('entry_time');
            $table->time('left_time');
            $table->time('interval_start_at');
            $table->time('interval_end_at');
            $table->timestamps();

            // Chave estrangeira apontando para o schema core
            $table->foreign('companyId')->references('id')->on('core.companies')->onDelete('cascade');
        });

        // 2. Tabela: job.person_companies
        Schema::create('job.person_companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('companyId');
            $table->uuid('personId');
            $table->enum('status', ['hired', 'experience', 'out']);
            $table->enum('role', ['employee', 'owner', 'humanResource', 'accountant']);
            $table->timestamps();

            // Chaves estrangeiras apontando para o schema core
            $table->foreign('companyId')->references('id')->on('core.companies')->onDelete('cascade');
            $table->foreign('personId')->references('id')->on('core.persons')->onDelete('cascade');
        });

        // 3. Tabela: job.employees
        Schema::create('job.employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('personCompanyId');
            $table->uuid('workloadId');
            $table->integer('registerNumber');
            $table->dateTime('register_at');
            $table->dateTime('left_at')->nullable();
            $table->timestamps();

            // Chaves estrangeiras internas (dentro do schema job)
            $table->foreign('personCompanyId')->references('id')->on('job.person_companies')->onDelete('cascade');
            $table->foreign('workloadId')->references('id')->on('job.workloads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop na ordem inversa por causa das chaves estrangeiras
        Schema::dropIfExists('job.employees');
        Schema::dropIfExists('job.person_companies');
        Schema::dropIfExists('job.workloads');

        // Opcional: Se quiser apagar o schema inteiro ao dar rollback (cuidado se houver outros dados lá)
        //DB::statement('DROP SCHEMA IF EXISTS job CASCADE');
    }
};
