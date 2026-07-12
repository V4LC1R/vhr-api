<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core.persons', function (Blueprint $table) {
            $table->string('pixKey')->nullable()->after('cellphone');
        });
    }

    public function down(): void
    {
        Schema::table('core.persons', function (Blueprint $table) {
            $table->dropColumn('pixKey');
        });
    }
};
