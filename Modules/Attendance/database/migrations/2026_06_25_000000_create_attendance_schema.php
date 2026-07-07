<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS attendance');

        // 1. attendance.daily_engagements (o dia consolidado por funcionário)
        Schema::create('attendance.daily_engagements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('companyId');
            $table->uuid('employeeId');
            $table->uuid('workloadId')->nullable();
            $table->date('date');
            $table->enum('type', ['work', 'day_off', 'holiday', 'medical', 'absence'])
                ->default('work');
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])
                ->default('draft');
            $table->integer('workedMinutes')->nullable();
            $table->integer('expectedMinutes')->nullable();
            $table->integer('balanceMinutes')->nullable();
            $table->decimal('diariaValue', 3, 1)->nullable();
            $table->string('note')->nullable();
            $table->uuid('draftedBy')->nullable();
            $table->uuid('approvedBy')->nullable();
            $table->dateTime('approvedAt')->nullable();
            $table->timestamps();

            $table->foreign('companyId')
                ->references('id')
                ->on('core.companies')
                ->onDelete('cascade');

            $table->foreign('employeeId')
                ->references('id')
                ->on('job.employees')
                ->onDelete('cascade');

            $table->foreign('workloadId')
                ->references('id')
                ->on('job.workloads')
                ->onDelete('set null');

            $table->foreign('draftedBy')
                ->references('id')
                ->on('core.user_companies')
                ->onDelete('set null');

            $table->foreign('approvedBy')
                ->references('id')
                ->on('core.user_companies')
                ->onDelete('set null');

            $table->unique(['companyId', 'employeeId', 'date']);
            $table->index('companyId');
            $table->index('employeeId');
            $table->index('status');
            $table->index('date');
        });

        // 2. attendance.time_entries (marcações — 1 linha por ação)
        Schema::create('attendance.time_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('companyId');
            $table->uuid('dailyEngagementId');
            $table->dateTime('punchedAt');
            $table->enum('type', ['entry', 'exit']);
            $table->enum('source', ['manual', 'device'])->default('manual');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('companyId')
                ->references('id')
                ->on('core.companies')
                ->onDelete('cascade');

            $table->foreign('dailyEngagementId')
                ->references('id')
                ->on('attendance.daily_engagements')
                ->onDelete('cascade');

            $table->index('dailyEngagementId');
            $table->index('companyId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance.time_entries');
        Schema::dropIfExists('attendance.daily_engagements');
    }
};
