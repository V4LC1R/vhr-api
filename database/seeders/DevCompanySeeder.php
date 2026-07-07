<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Attendance\Models\TimeEntry;
use Modules\Core\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
use Modules\Core\Models\User;
use Modules\Core\Models\UserCompany;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Employment;
use Modules\Job\Models\Workload;
use Spatie\Permission\PermissionRegistrar;

/**
 * Popula uma empresa completa para uso LOCAL/manual: usuários para login,
 * funcionários com vínculo, jornadas e lançamentos de ponto.
 *
 * NÃO roda em testes: só é chamado pelo DatabaseSeeder raiz — que o
 * seedModules() dos testes nunca invoca — e ainda trava por ambiente.
 *
 * Idempotente: rodar de novo não duplica (upserts + guarda de estrutura).
 */
class DevCompanySeeder extends Seeder
{
    private const SENHA = 'password';

    public function run(): void
    {
        if (app()->environment('production', 'testing')) {
            $this->command?->warn('DevCompanySeeder ignorado no ambiente: ' . app()->environment());

            return;
        }

        // Papéis/permissões precisam existir antes de atribuir os roles.
        $this->call(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $company = Company::firstOrCreate(
            ['cnpj' => '12345678000199'],
            ['name' => 'Empresa Demo'],
        );

        $this->criarUsuarios($company);
        $this->criarEstrutura($company);

        $this->command?->info("✅ Empresa Demo pronta (id: {$company->id}).");
        $this->command?->info('   Logins (senha "' . self::SENHA . '"): owner@demo.test | rh@demo.test | contador@demo.test');
    }

    /**
     * Usuários que conseguem logar, cada um com um papel diferente.
     */
    private function criarUsuarios(Company $company): void
    {
        $usuarios = [
            ['email' => 'owner@demo.test',    'role' => 'owner',         'name' => 'Ana Owner'],
            ['email' => 'rh@demo.test',       'role' => 'humanResource', 'name' => 'Rui RH'],
            ['email' => 'contador@demo.test', 'role' => 'accountant',    'name' => 'Cida Contadora'],
        ];

        foreach ($usuarios as $i => $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                ['password' => self::SENHA, 'status' => 'active'],
            );

            $person = Person::firstOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'cellphone' => '(11) 99999-00' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)],
            );

            $userCompany = UserCompany::firstOrCreate(
                ['userId' => $user->id, 'companyId' => $company->id],
                ['personId' => $person->id],
            );

            app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
            $userCompany->syncRoles([$u['role']]);
        }
    }

    /**
     * Jornadas, funcionários com vínculo ativo e alguns dias de ponto.
     */
    private function criarEstrutura(Company $company): void
    {
        // Só popula a estrutura pesada uma vez por empresa (idempotência).
        if (Employee::query()->where('companyId', $company->id)->exists()) {
            return;
        }

        $ownerUc = UserCompany::query()
            ->where('companyId', $company->id)
            ->whereHas('user', fn ($q) => $q->where('email', 'owner@demo.test'))
            ->first();

        $jornadas = collect([
            ['description' => 'Jornada 44h (Seg-Sex 08-18)', 'weeklyHours' => 44, 'monthlyHours' => 220],
            ['description' => 'Meio período 22h',            'weeklyHours' => 22, 'monthlyHours' => 110],
        ])->map(fn (array $j) => Workload::factory()->create([
            ...$j,
            'companyId'         => $company->id,
            'entryTime'        => '08:00:00',
            'leftTime'         => '18:00:00',
            'intervalStartAt' => '12:00:00',
            'intervalEndAt'   => '13:00:00',
        ]));

        $funcionarios = [
            ['nome' => 'Bruno Alves',  'kind' => EmploymentTypeEnum::CLT],
            ['nome' => 'Carla Dias',   'kind' => EmploymentTypeEnum::CLT],
            ['nome' => 'Diego Farias', 'kind' => EmploymentTypeEnum::CLT],
            ['nome' => 'Elisa Gomes',  'kind' => EmploymentTypeEnum::TEMPORARY],
            ['nome' => 'Fabio Horta',  'kind' => EmploymentTypeEnum::FREELANCER],
            ['nome' => 'Gina Lima',    'kind' => EmploymentTypeEnum::DAYLI],
        ];

        $registerNumber = 1;

        foreach ($funcionarios as $i => $f) {
            $person = Person::create([
                'name'      => $f['nome'],
                'email'     => 'func' . ($i + 1) . '@demo.test',
                'cellphone' => '(11) 98888-00' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
            ]);

            $employee = Employee::create([
                'companyId'      => $company->id,
                'personId'       => $person->id,
                'registerNumber' => $registerNumber++,
            ]);

            Employment::create([
                'employeeId'  => $employee->id,
                'workloadId'  => $jornadas->first()->id,
                'kind'        => $f['kind']->value,
                'status'      => EmploymentStatusEnum::HIRED->value,
                'registerAt' => now()->utc(),
            ]);

            // Ponto apenas para os 3 primeiros, o bastante para popular relatórios.
            if ($i < 3) {
                $this->criarPonto($company, $employee, $ownerUc);
            }
        }
    }

    /**
     * 5 dias úteis recentes com entrada/saída e status variados
     * (aprovado, pendente e um rascunho do owner).
     */
    private function criarPonto(Company $company, Employee $employee, ?UserCompany $ownerUc): void
    {
        for ($d = 1; $d <= 5; $d++) {
            $date = now()->subDays($d);

            if ($date->isWeekend()) {
                continue;
            }

            [$status, $draftedBy] = match (true) {
                $d <= 2  => ['approved', null],
                $d === 3 => ['pending', null],
                default  => ['draft', $ownerUc?->id],
            };

            $day = DailyEngagement::factory()->create([
                'companyId'  => $company->id,
                'employeeId' => $employee->id,
                'date'       => $date->toDateString(),
                'status'     => $status,
                'draftedBy'  => $draftedBy,
            ]);

            // Entrada 08:00 e saída 17:00 (armazenadas em UTC).
            TimeEntry::factory()->create([
                'companyId'         => $company->id,
                'dailyEngagementId' => $day->id,
                'punchedAt'        => $date->copy()->setTime(11, 0)->utc(),
                'type'              => 'entry',
            ]);

            TimeEntry::factory()->create([
                'companyId'         => $company->id,
                'dailyEngagementId' => $day->id,
                'punchedAt'        => $date->copy()->setTime(20, 0)->utc(),
                'type'              => 'exit',
            ]);
        }
    }
}
