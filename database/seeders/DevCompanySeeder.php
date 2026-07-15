<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Attendance\Models\TimeEntry;
use Modules\Attendance\Support\AttendanceCalculator;
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
            ['email' => 'owner@demo.test',    'role' => 'owner',         'name' => 'Ana Owner',      'cpf' => '10020030088'],
            ['email' => 'rh@demo.test',       'role' => 'humanResource', 'name' => 'Rui RH',         'cpf' => '10020030169'],
            ['email' => 'contador@demo.test', 'role' => 'accountant',    'name' => 'Cida Contadora', 'cpf' => '10020030240'],
        ];

        foreach ($usuarios as $i => $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                ['password' => self::SENHA, 'status' => 'active'],
            );

            $person = Person::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name'      => $u['name'],
                    'cpf'       => $u['cpf'],
                    'cellphone' => '(11) 99999-00' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                ],
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
            ['nome' => 'Bruno Alves',  'kind' => EmploymentTypeEnum::CLT,        'cpf' => '10020030320'],
            ['nome' => 'Carla Dias',   'kind' => EmploymentTypeEnum::CLT,        'cpf' => '10020030401'],
            ['nome' => 'Diego Farias', 'kind' => EmploymentTypeEnum::CLT,        'cpf' => '10020030592'],
            ['nome' => 'Elisa Gomes',  'kind' => EmploymentTypeEnum::TEMPORARY,  'cpf' => '10020030673'],
            ['nome' => 'Fabio Horta',  'kind' => EmploymentTypeEnum::FREELANCER, 'cpf' => '10020030754'],
            ['nome' => 'Gina Lima',    'kind' => EmploymentTypeEnum::DAYLI,      'cpf' => '10020030835'],
        ];

        $registerNumber = 1;

        foreach ($funcionarios as $i => $f) {
            $person = Person::create([
                'name'      => $f['nome'],
                'cpf'       => $f['cpf'],
                'email'     => 'func' . ($i + 1) . '@demo.test',
                'cellphone' => '(11) 98888-00' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
            ]);

            $employee = Employee::create([
                'companyId'      => $company->id,
                'personId'       => $person->id,
                'registerNumber' => $registerNumber++,
            ]);

            $workload = $jornadas->first();

            Employment::create([
                'employeeId'  => $employee->id,
                'workloadId'  => $workload->id,
                'kind'        => $f['kind']->value,
                'status'      => EmploymentStatusEnum::HIRED->value,
                'registerAt' => now()->utc(),
            ]);

            // Ponto pra todo mundo — dá massa real pros 3 relatórios (geral,
            // faltas/horas negativas e diaristas/temporários).
            $this->criarPonto($company, $employee, $workload, $ownerUc);
        }
    }

    /**
     * 5 dias úteis recentes com entrada/saída e status variados (aprovado,
     * pendente e um rascunho do owner) — sempre recalculados pelo
     * `AttendanceCalculator`, igual ao fluxo real de lançar ponto pela tela.
     * Sem isso o dia fica com workloadId nulo e worked/expected/balance zerados.
     */
    private function criarPonto(Company $company, Employee $employee, Workload $workload, ?UserCompany $ownerUc): void
    {
        $calculator = app(AttendanceCalculator::class);

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
                'workloadId' => $workload->id,
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

            $calculator->recalculate($day);
        }
    }
}
