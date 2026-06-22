<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Models\Person;
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
            'name' => 'Bruce Wayne',
            'email' => 'bruce@waynecorp.com',
            'cellphone' => '11988888888',
        ];
        $this->postJson('/api/v1/persons', $payload)
            ->assertUnauthorized();
    }

    public function testDeveRetornarForbiddenQuandoUsuarioNaoPossuirPermissaoDeCriacao()
    {
        $this->autenticarSemPermissao();

        $payload = [
            'name' => 'Bruce Wayne',
            'email' => 'bruce@waynecorp.com',
            'cellphone' => '11988888888',
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
                'name',
                'email',
                'cellphone',
            ]);
    }

    public function testDeveRetornarErroDeValidacaoQuandoEmailForInvalido()
    {
        $this->autenticarComPermissao('core.persons.create');

        $payload = [
            'name' => 'Bruce Wayne',
            'email' => 'email-invalido',
            'cellphone' => '11988888888',
        ];

        $this->postJson('/api/v1/persons', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    public function testDeveRetornarConflitoQuandoEmailJaEstiverCadastrado()
    {
        $this->autenticarComPermissao('core.persons.create');

        Person::factory()->create([
            'email' => 'bruce@waynecorp.com',
        ]);

        $payload = [
            'name' => 'Bruce Wayne',
            'email' => 'bruce@waynecorp.com',
            'cellphone' => '11988888888',
        ];

        $this->postJson('/api/v1/persons', $payload)
            ->assertStatus(409);
    }

    public function testDeveCadastrarUmaPessoaComSucesso()
    {
        $this->autenticarComPermissao('core.persons.create');

        $payload = [
            'name' => 'Bruce Wayne',
            'email' => 'bruce@waynecorp.com',
            'cellphone' => '11988888888',
        ];

        $this->postJson('/api/v1/persons', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('core.persons', [
            'email' => 'bruce@waynecorp.com',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function testDeveRetornarUnauthorizedAoConsultarPessoaSemAutenticacao()
    {
        $person = Person::factory()->create();

        $this->getJson("/api/v1/persons/{$person->id}")
            ->assertUnauthorized();
    }

    public function testDeveRetornarForbiddenAoConsultarPessoaSemPermissao()
    {
        $person = Person::factory()->create();

        $this->autenticarSemPermissao();

        $this->getJson("/api/v1/persons/{$person->id}")
            ->assertForbidden();
    }

    public function testDeveRetornarNotFoundQuandoPessoaNaoExistir()
    {
        $this->autenticarComPermissao('core.persons.view');

        $this->getJson('/api/v1/persons/' . fake()->uuid())
            ->assertNotFound();
    }

    public function testDeveConsultarUmaPessoaComSucesso()
    {
        $this->autenticarComPermissao('core.persons.view');

        $person = Person::factory()->create();

        $this->getJson("/api/v1/persons/{$person->id}")
            ->assertOk();
    }

    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */

    public function testDeveRetornarUnauthorizedAoListarPessoasSemAutenticacao()
    {
        $this->getJson('/api/v1/persons')
            ->assertUnauthorized();
    }

    public function testDeveRetornarForbiddenAoListarPessoasSemPermissao()
    {
        $this->autenticarSemPermissao();

        $this->getJson('/api/v1/persons')
            ->assertForbidden();
    }

    public function testDeveListarPessoasComSucesso()
    {
        $this->autenticarComPermissao('core.persons.view');

        Person::factory()->count(5)->create();

        $this->getJson('/api/v1/persons')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'current_page',
            'per_page',
            'total',
        ]);
    }

    public function testDeveFiltrarPessoasPorNome()
    {
        $this->autenticarComPermissao('core.persons.view');

        Person::factory()->create(['name' => 'Bruce Wayne']);
        Person::factory()->create(['name' => 'Clark Kent']);

        $this->getJson('/api/v1/persons?name=Bruce')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Bruce Wayne');
    }

    public function testDevePaginarPersons()
    {
        $this->autenticarComPermissao('core.persons.view');

        Person::factory()->count(20)->create();

        $this->getJson('/api/v1/persons?per_page=5')
            ->assertOk()
            ->assertJsonPath('per_page', 5)
            ->assertJsonCount(5, 'data');
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

    public function testDeveRetornarConflitoAoAtualizarComEmailJaExistente()
    {
        $this->autenticarComPermissao('core.persons.update');

        $personA = Person::factory()->create([
            'email' => 'bruce@waynecorp.com',
        ]);

        $personB = Person::factory()->create([
            'email' => 'clark@dailyplanet.com',
        ]);

        $this->putJson("/api/v1/persons/{$personB->id}", [
            'name' => $personB->name,
            'email' => $personA->email,
            'cellphone' => $personB->cellphone,
        ])->assertStatus(409);
    }

    public function testDeveAtualizarUmaPessoaComSucesso()
    {
        $this->autenticarComPermissao('core.persons.update');

        $person = Person::factory()->create();

        $this->putJson("/api/v1/persons/{$person->id}", [
            'name' => 'Batman',
            'email' => $person->email,
            'cellphone' => $person->cellphone,
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

    public function testDeveRetornarUnauthorizedAoRemoverPessoaSemAutenticacao()
    {
        $person = Person::factory()->create();

        $this->deleteJson("/api/v1/persons/{$person->id}")
            ->assertUnauthorized();
    }

    public function testDeveRetornarForbiddenAoRemoverPessoaSemPermissao()
    {
        $person = Person::factory()->create();

        $this->autenticarSemPermissao();

        $this->deleteJson("/api/v1/persons/{$person->id}")
            ->assertForbidden();
    }

    public function testDeveRetornarNotFoundAoRemoverPessoaInexistente()
    {
        $this->autenticarComPermissao('core.persons.delete');

        $this->deleteJson('/api/v1/persons/' . fake()->uuid())
            ->assertNotFound();
    }

    public function testDeveRemoverUmaPessoaComSucesso()
    {
        $this->autenticarComPermissao('core.persons.delete');

        $person = Person::factory()->create();

        $this->deleteJson("/api/v1/persons/{$person->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('core.persons', [
            'id' => $person->id,
        ]);
    }
}
