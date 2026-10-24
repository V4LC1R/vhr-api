<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
use Modules\Core\Models\User;
use Modules\Job\Models\Employee;
use Tests\DBTestCase;

class PersonTest extends DBTestCase
{
    protected bool $seed = true;

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function testDeveRetornarUnauthorizedQuandoUsuarioNaoEstiverAutenticado()
    {
        $payload = [
            'cpf' => '52998224725',
            'name' => 'Bruce Wayne',
            'email' => 'bruce@waynecorp.com',
            'cellphone' => '11988888888',
            'birhtdate' => now()
        ];
        $this->postJson('/api/v1/persons', $payload)
            ->assertUnauthorized();
    }

    public function testDeveRetornarForbiddenQuandoUsuarioNaoPossuirPermissaoDeCriacao()
    {
        $this->autenticarSemPermissao();

        $payload = [
            'cpf' => '52998224725',
            'name' => 'Bruce Wayne',
            'email' => 'bruce@waynecorp.com',
            'cellphone' => '11988888888',
            'birhtdate' => now()
        ];

        $this->postJson('/api/v1/persons', $payload)
            ->assertForbidden();
    }

    public function testDeveRetornarErroDeValidacaoQuandoCamposObrigatoriosEstiveremAusentes()
    {
        $this->autenticarComPermissao('core.persons.create');

        $this->postJson('/api/v1/persons', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'cpf',
                'name',
                'email',
                'cellphone',
                'birthdate'
            ]);
    }

    public function testDeveRetornarErroDeValidacaoQuandoEmailForInvalido()
    {
        $this->autenticarComPermissao('core.persons.create');

        $payload = [
            'cpf' => '52998224725',
            'name' => 'Bruce Wayne',
            'email' => 'email-invalido',
            'cellphone' => '11988888888',
            'birhtdate' => now()
        ];

        $this->postJson('/api/v1/persons', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    public function testDeveRetornarErroDeValidacaoQuandoCpfForInvalido()
    {
        $this->autenticarComPermissao('core.persons.create');

        $payload = [
            'cpf' => '52998224724', // dígito verificador incorreto
            'name' => 'Bruce Wayne',
            'email' => 'bruce@waynecorp.com',
            'cellphone' => '11988888888',
            'birhtdate' => now()
        ];

        $this->postJson('/api/v1/persons', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'cpf',
            ]);
    }

    /** @test */
    public function testDeveRetornarUnprocessableEntityQuandoEmailJaEstiverCadastrado()
    {
        $this->autenticarComPermissao('core.persons.create');

        Person::factory()->create([
            'email' => 'bruce@waynecorp.com',
        ]);

        $payload = [
            'cpf' => '52998224725',
            'name' => 'Bruce Wayne',
            'email' => 'bruce@waynecorp.com',
            'cellphone' => '11988888888',
            'birthdate' => now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP')
        ];

        $this->postJson('/api/v1/persons', $payload)
            ->assertStatus(422);
    }

    public function testDeveRetornarUnprocessableEntityQuandoCpfJaEstiverCadastrado()
    {
        $this->autenticarComPermissao('core.persons.create');

        Person::factory()->create([
            'cpf' => '52998224725',
        ]);

        $payload = [
            'cpf' => '52998224725',
            'name' => 'Bruce Wayne',
            'email' => 'bruce@waynecorp.com',
            'cellphone' => '11988888888',
            'birthdate' => now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP')
        ];

        $this->postJson('/api/v1/persons', $payload)
            ->assertStatus(409);
    }

    public function testDeveCadastrarUmaPessoaComSucesso()
    {
        $this->autenticarComPermissao('core.persons.create');

        $payload = [
            'cpf' => '52998224725',
            'name' => 'Bruce Wayne',
            'email' => 'bruce@waynecorp.com',
            'cellphone' => '11988888888',
            'pixKey' => 'bruce@waynecorp.com',
            'birthdate' => now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP')
        ];

        $response = $this->postJson('/api/v1/persons', $payload)
            ->assertCreated();

        $response->assertJsonPath('pixKey', 'bruce@waynecorp.com');

        $this->assertDatabaseHas('core.persons', [
            'cpf' => '52998224725',
            'email' => 'bruce@waynecorp.com',
            'pixKey' => 'bruce@waynecorp.com',
        ]);
    }

    public function testDeveCadastrarUmaPessoaSemChavePixComSucesso()
    {
        $this->autenticarComPermissao('core.persons.create');

        $payload = [
            'cpf' => '52998224725',
            'name' => 'Bruce Wayne',
            'email' => 'bruce@waynecorp.com',
            'cellphone' => '11988888888',
            'birthdate' => now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP')
        ];

        $response = $this->postJson('/api/v1/persons', $payload)
            ->assertCreated();

        $response->assertJsonPath('pixKey', null);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function testDeveRetornarUnauthorizedAoAtualizarPessoaSemAutenticacao()
    {
        $person = Person::factory()->create();

        $this->putJson("/api/v1/persons/{$person->id}", [
            'name' => 'Batman',
        ])->assertUnauthorized();
    }

    public function testDeveRetornarForbiddenAoAtualizarPessoaSemPermissao()
    {
        $person = Person::factory()->create();

        $this->autenticarSemPermissao();

        $this->putJson("/api/v1/persons/{$person->id}", [
            'name' => 'Batman',
        ])->assertForbidden();
    }

    public function testDeveRetornarNotFoundAoAtualizarPessoaInexistente()
    {
        $this->autenticarComPermissao('core.persons.update');

        $this->putJson('/api/v1/persons/' . fake()->uuid(), [
            'name' => 'Batman',
        ])->assertNotFound();
    }

    public function testDeveRetornarAoAtualizarComEmailJaExistente()
    {
        $rh = User::factory()->create();
        $company = Company::factory()->create();

        $personA = Person::factory()->create([
            'email' => 'bruce@waynecorp.com',
        ]);

        Employee::factory()->create([
            'companyId' => $company->id,
            'personId' =>  $personA->id,
        ]);

        $personB = Person::factory()->create([
            'email' => 'clark@dailyplanet.com',
        ]);

        Employee::factory()->create([
            'companyId' => $company->id,
            'personId' =>  $personB->id,
        ]);

        $this->autenticarComPermissao(
            'core.persons.update',
            user: $rh,
            company: $company,
            person: $personB
        );

        $this->putJson("/api/v1/persons/{$personB->id}", [
            'cpf' => $personB->cpf,
            'name' => $personB->name,
            'email' => $personA->email,
            'cellphone' => $personB->cellphone,
            'birthdate' => now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP')
        ])->assertStatus(422);
    }

    public function testDeveRetornarUnprocessableEntityAoAtualizarComCpfJaExistente()
    {
        $rh = User::factory()->create();
        $company = Company::factory()->create();

        $personA = Person::factory()->create([
            'cpf' => '52998224725',
        ]);

        Employee::factory()->create([
            'companyId' => $company->id,
            'personId' =>  $personA->id,
        ]);

        $personB = Person::factory()->create([
            'cpf' => '11144477735',
        ]);

        Employee::factory()->create([
            'companyId' => $company->id,
            'personId' =>  $personB->id,
        ]);

        $this->autenticarComPermissao(
            'core.persons.update',
            user: $rh,
            company: $company,
            person: $personB
        );

        $this->putJson("/api/v1/persons/{$personB->id}", [
            'cpf' => $personA->cpf,
            'name' => $personB->name,
            'email' => $personB->email,
            'cellphone' => $personB->cellphone,
            'birthdate' => now()
                ->timezone('Europe/Stockholm')
                ->format('Y-m-d\TH:i:sP'),
        ])->assertStatus(422);
    }

    public function testDeveRetornarForbiddenAoAtualizarPessoaSemVinculoComAEmpresa()
    {
        $company = Company::factory()->create();

        $person = Person::factory()->create();

        $this->autenticarComPermissao('core.persons.update');

        Employee::factory()->create([
            'companyId' => $company->id,
            'personId' =>  $person->id,
        ]);

        $this->putJson("/api/v1/persons/{$person->id}", [
            'cpf' => $person->cpf,
            'name' => 'Batman',
            'email' => $person->email,
            'cellphone' => $person->cellphone,
            'birthdate' =>  now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP')
        ])->assertForbidden();
    }

    public function testDeveAtualizarUmaPessoaComSucesso()
    {
        $company = Company::factory()->create();

        $person = Person::factory()->create();

        $this->autenticarComPermissao('core.persons.update', company:$company);

        Employee::factory()->create([
            'companyId' => $company->id,
            'personId' =>  $person->id,
        ]);

        $this->putJson("/api/v1/persons/{$person->id}", [
            'cpf' => $person->cpf,
            'name' => 'Batman',
            'email' => $person->email,
            'cellphone' => $person->cellphone,
            'birthdate' =>  now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP')
        ])->assertOk();

        $this->assertDatabaseHas('core.persons', [
            'id' => $person->id,
            'name' => 'Batman',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function testDeveRetonarForbiddenAoTentarRemoverPessoa()
    {
        $this->autenticarComPermissao('core.persons.delete');

        $person = Person::factory()->create();

        $this->deleteJson("/api/v1/persons/{$person->id}")
            ->assertForbidden();
    }
}
