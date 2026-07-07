<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS job');

        // 1. job.workloads
        Schema::create('job.workloads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('companyId');
            $table->string('description');
            $table->integer('monthlyHours');
            $table->integer('weeklyHours');
            $table->time('entryTime');
            $table->time('leftTime');
            $table->time('intervalStartAt');
            $table->time('intervalEndAt');
            $table->timestamps();

            $table->foreign('companyId')
                ->references('id')
                ->on('core.companies')
                ->onDelete('cascade');
        });

        // 2. job.employees (identidade do funcionário)
        Schema::create('job.employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('companyId');
            $table->uuid('personId');
            $table->unsignedInteger('registerNumber');
            $table->timestamps();

            $table->foreign('companyId')
                ->references('id')
                ->on('core.companies')
                ->onDelete('cascade');

            $table->foreign('personId')
                ->references('id')
                ->on('core.persons')
                ->onDelete('cascade');

            $table->unique(['companyId', 'personId']);
            $table->unique(['companyId', 'registerNumber']);
            $table->index('companyId');
            $table->index('personId');
        });

        // 3. job.employments (vínculo contratual)
        Schema::create('job.employments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employeeId');
            $table->uuid('workloadId');
            $table->enum('kind', ['clt', 'dayli', 'temporary', 'freelancer'])
                ->default('clt');
            $table->enum('status', ['hired', 'experience', 'left'])
                ->default('experience');
            $table->dateTime('registerAt');
            $table->dateTime('leftAt')->nullable();
            $table->timestamps();

            $table->foreign('employeeId')
                ->references('id')
                ->on('job.employees')
                ->onDelete('cascade');

            $table->foreign('workloadId')
                ->references('id')
                ->on('job.workloads')
                ->onDelete('cascade');

            $table->index('employeeId');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job.employments');
        Schema::dropIfExists('job.employees');
        Schema::dropIfExists('job.workloads');
    }
};
