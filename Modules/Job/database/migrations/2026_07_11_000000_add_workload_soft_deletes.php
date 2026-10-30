<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job.workloads', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('job.employments', function (Blueprint $table) {
            $table->softDeletes();
            $table->dropForeign(['workloadId']);
            $table->foreign('workloadId')
                ->references('id')
                ->on('job.workloads')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job.employments', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['workloadId']);
            $table->foreign('workloadId')
                ->references('id')
                ->on('job.workloads')
                ->onDelete('cascade');
        });

        Schema::table('job.workloads', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
