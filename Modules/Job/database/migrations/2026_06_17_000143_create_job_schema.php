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

            $table->foreign('companyId')
                ->references('id')
                ->on('core.companies')
                ->onDelete('cascade');
        });


        // 2. Tabela: job.employees
        Schema::create('job.employees', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('companyId');
            $table->uuid('personId');

            $table->enum('status', [
                'hired',
                'experience',
                'out'
            ]);

            $table->enum('role', [
                'employee',
                'owner',
                'humanResource',
                'accountant'
            ]);

            $table->uuid('workloadId');

            $table->unsignedInteger('registerNumber');

            $table->dateTime('register_at');
            $table->dateTime('left_at')->nullable();

            $table->timestamps();

            $table->foreign('companyId')
                ->references('id')
                ->on('core.companies')
                ->onDelete('cascade');

            $table->foreign('personId')
                ->references('id')
                ->on('core.persons')
                ->onDelete('cascade');

            $table->foreign('workloadId')
                ->references('id')
                ->on('job.workloads')
                ->onDelete('cascade');

            $table->index('companyId');
            $table->index('personId');
            $table->index([
                'companyId',
                'status',
            ]);
            $table->index('role');

            $table->unique([
                'companyId',
                'registerNumber',
            ]);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job.employees');
        Schema::dropIfExists('job.workloads');
    }
};
