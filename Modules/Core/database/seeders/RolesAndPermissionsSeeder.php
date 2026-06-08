<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpar cache do Spatie Permission para evitar conflitos em seeds repetidos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Definir as Permissões focadas nas tabelas Atuais (Schema Core)
        $permissions = [
            // Módulo: Persons
            'core.persons.view',
            'core.persons.create',
            'core.persons.update',
            'core.persons.delete',

            // Módulo: Companies
            'core.companies.view',
            'core.companies.create',
            'core.companies.update',
            'core.companies.delete',

            // Módulo: Users
            'core.users.view',
            'core.users.create',
            'core.users.update',
            'core.users.delete',

            // Futuros escopos baseados no seu DER (Deixando gatilhos prontos)
            'job.workload.manage',
            'attendance.timeEntries.manage',
            'attendance.dailyEngagements.view',
        ];

        // Criar permissões se não existirem
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 3. Definir as Roles baseadas exatamente no enum 'PersonCompanyRole' do seu DER

        // Papel: Owner (Dono da Empresa - Acesso Total)
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $ownerRole->syncPermissions(Permission::all()); // Dono pode tudo

        // Papel: Human Resource (RH - Foco em Pessoas, Empresas e Escalas)
        $hrRole = Role::firstOrCreate(['name' => 'humanResource', 'guard_name' => 'web']);
        $hrRole->syncPermissions([
            'core.persons.view',
            'core.persons.create',
            'core.persons.update',
            'core.companies.view',
            'job.workload.manage',
            'attendance.timeEntries.manage'
        ]);

        // Papel: Accountant (Contador - Foco em Visualização e Auditoria)
        $accountantRole = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountantRole->syncPermissions([
            'core.persons.view',
            'core.companies.view',
            'attendance.dailyEngagements.view'
        ]);

        // Papel: Employee (Funcionário comum - Acesso apenas aos seus dados / auto-atendimento)
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employeeRole->syncPermissions([
            'core.persons.view', // Geralmente restrito ao próprio ID via política (Policy)
        ]);
    }
}
