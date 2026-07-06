<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
use Modules\Core\Models\User;
use Modules\Core\Models\UserCompany;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Provisiona uma nova empresa em etapas: nome → CNPJ → email do owner.
 * Se o email já pertence a um usuário, pergunta se deve vincular o
 * usuário existente como owner; caso contrário, cria o usuário (com
 * senha gerada), a pessoa e o vínculo owner da nova empresa.
 */
class CreateCompanyCommand extends Command
{
    protected $signature = 'company:create';

    protected $description = 'Cria uma nova empresa e define o owner (usuário novo ou existente), em etapas.';

    public function handle(): int
    {
        // A role owner precisa existir para ser atribuída ao vínculo.
        if (! Role::where('name', 'owner')->where('guard_name', 'web')->exists()) {
            $this->components->error("A role 'owner' não existe. Rode as permissões primeiro (ex.: php artisan dev:seed).");

            return self::FAILURE;
        }

        // Etapa 1 — nome da empresa.
        $name = $this->askValidated('Nome da empresa', 'name', [
            'required', 'string', 'min:5', Rule::unique(Company::class, 'name'),
        ]);

        // Etapa 2 — CNPJ (formato mínimo + duplicidade), mesma regra da API.
        $cnpj = $this->askValidated('CNPJ', 'cnpj', [
            'required', 'string', 'min:14', Rule::unique(Company::class, 'cnpj'),
        ]);

        // Etapa 3 — email do owner.
        $email = $this->askValidated('Email do owner', 'email', ['required', 'email']);

        $existingUser = User::firstWhere('email', $email);
        $existingPerson = Person::firstWhere('email', $email);

        // Email já em uso: confirmar antes de vincular o usuário existente.
        if ($existingUser) {
            $this->components->warn("Já existe um usuário com o email {$email}.");

            if (! $this->confirm('Deseja continuar e vincular esse usuário como owner da nova empresa?', false)) {
                $this->components->info('Operação cancelada. Nada foi criado.');

                return self::SUCCESS;
            }
        }

        // Precisamos dos dados da pessoa apenas quando ela ainda não existe.
        $ownerName = null;
        $cellphone = null;

        if (! $existingPerson) {
            $ownerName = $this->askValidated('Nome do owner', 'name', ['required', 'string', 'min:3']);
            $cellphone = $this->askValidated('Celular do owner', 'cellphone', ['required', 'string', 'min:8']);
        }

        // Usuário novo recebe uma senha gerada (exibida uma única vez).
        $generatedPassword = $existingUser ? null : Str::password(16);

        [$company] = DB::transaction(function () use ($name, $cnpj, $email, $existingUser, $existingPerson, $ownerName, $cellphone, $generatedPassword) {
            $company = Company::create(['name' => $name, 'cnpj' => $cnpj]);

            $user = $existingUser ?? User::create([
                'email'    => $email,
                'password' => $generatedPassword, // cast 'hashed' no model
                'status'   => 'active',
            ]);

            $person = $existingPerson ?? Person::create([
                'name'      => $ownerName,
                'email'     => $email,
                'cellphone' => $cellphone,
            ]);

            $userCompany = UserCompany::create([
                'companyId' => $company->id,
                'userId'    => $user->id,
                'personId'  => $person->id,
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
            $userCompany->syncRoles(['owner']);

            return [$company, $user, $person];
        });

        $this->newLine();
        $this->components->info('Empresa criada com sucesso.');
        $this->table(['Campo', 'Valor'], [
            ['Empresa', $company->name],
            ['CNPJ', $company->cnpj],
            ['Company ID', $company->id],
            ['Owner', $email],
            ['Papel', 'owner'],
        ]);

        if ($generatedPassword) {
            $this->components->warn("Senha gerada para {$email}: {$generatedPassword}");
            $this->components->warn('Guarde agora — ela não será exibida novamente.');
        } else {
            $this->components->info('Usuário existente vinculado — a senha dele permanece inalterada.');
        }

        return self::SUCCESS;
    }

    /**
     * Pergunta um valor e revalida em loop até passar nas regras.
     */
    private function askValidated(string $label, string $field, array $rules): string
    {
        while (true) {
            $value = trim((string) $this->ask($label));

            $validator = Validator::make([$field => $value], [$field => $rules]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->components->error($error);
                }

                continue;
            }

            return $value;
        }
    }
}
