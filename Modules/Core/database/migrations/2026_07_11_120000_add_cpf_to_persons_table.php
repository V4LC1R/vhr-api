<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core.persons', function (Blueprint $table) {
            // Nullable para não quebrar linhas já existentes; obrigatório é
            // imposto na camada de validação (StorePersonRequest/UpdatePersonRequest).
            // Unique aqui (não via Rule::unique no FormRequest) é o que alimenta o
            // catch de QueryException -> UniqueConstraintException no PersonService,
            // no mesmo padrão já usado pela coluna 'email'.
            $table->string('cpf', 11)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('core.persons', function (Blueprint $table) {
            $table->dropColumn('cpf');
        });
    }
};
