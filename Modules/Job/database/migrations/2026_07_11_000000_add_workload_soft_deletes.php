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

        // A exclusão de jornada via API é soft delete — vínculos encerrados
        // continuam referenciando a jornada (histórico). O restrict substitui o
        // cascade pra nenhum hard delete direto no banco apagar vínculos junto.
        Schema::table('job.employments', function (Blueprint $table) {
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
