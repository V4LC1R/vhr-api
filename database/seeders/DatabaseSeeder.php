<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed da aplicação (uso local via `php artisan db:seed`).
     *
     * Os testes NÃO passam por aqui — eles rodam os {Module}DatabaseSeeder
     * diretamente — então a massa de demonstração fica isolada do CI.
     *
     * Sem WithoutModelEvents de propósito: o HasUuids depende do evento
     * `creating` para gerar os UUIDs.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $this->call(DevCompanySeeder::class);
    }
}
