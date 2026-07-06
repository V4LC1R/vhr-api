<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Popula o banco LOCAL com a massa de demonstração (DevCompanySeeder):
 * Empresa Demo + usuários de login (owner/rh/contador), jornadas,
 * funcionários com vínculo e alguns dias de ponto.
 *
 * Seed-only por padrão (idempotente, não duplica). Com --fresh, recria
 * o banco antes. Bloqueado em produção.
 */
class DevSeedCommand extends Command
{
    protected $signature = 'dev:seed
        {--fresh : Recria o banco (migrate:fresh) antes de semear — APAGA todos os dados}
        {--force : Pula a confirmação do --fresh (para ambientes não-interativos)}';

    protected $description = 'Popula o banco local com a massa de demonstração. Bloqueado em produção.';

    public function handle(): int
    {
        if ($this->getLaravel()->environment('production')) {
            $this->components->error('dev:seed não roda em produção.');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            if (! $this->option('force') && ! $this->confirm('Isso vai APAGAR todos os dados e recriar o banco. Continuar?')) {
                $this->components->warn('Cancelado.');

                return self::FAILURE;
            }

            $this->call('migrate:fresh', ['--force' => true]);
        }

        // Passa pelo DatabaseSeeder raiz (que chama o DevCompanySeeder e
        // também trava em produção). --force evita o prompt interativo.
        $this->call('db:seed', ['--force' => true]);

        return self::SUCCESS;
    }
}
