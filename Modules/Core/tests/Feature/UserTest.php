<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Models\Person;
use Modules\Core\Models\User;
use Tests\DBTestCase;

class UserTest extends DBTestCase
{
    protected bool $seed = true;
    /*
    |--------------------------------------------------------------------------
    | CREATE (Cadastro de Usuário por um Admin/Operador)
    |--------------------------------------------------------------------------
    */

    public function testDeveRetornarUnauthorizedAoCadastrarUsuarioSemAutenticacao()
    {
        $this->postJson('/api/v1/users', [])
            ->assertUnauthorized();
    }

    public function testDeveRetornarForbiddenAoCadastrarUsuarioSemPermissao()
    {
        $this->autenticarSemPermissao();

        $this->postJson('/api/v1/users', [])
            ->assertForbidden();
    }

    public function testDeveRetornarErroDeValidacaoQuandoCamposObrigatoriosDeUsuarioEstiveremAusentes()
    {
        $this->autenticarComPermissao('core.users.create');

        $this->postJson('/api/v1/users', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
                'password'
            ]);
    }

    public function testDeveRetornarConflitoSeUsernameOuEmailJaExistirem()
    {
        $this->autenticarComPermissao('core.users.create');

        User::factory()->create([
            'email'    => 'bruce@waynecorp.com',
        ]);

        $person = Person::factory()->create();

        $payload = [
            'email'     => 'bruce@waynecorp.com',
            'password'  => 'Secret123',
            'personId'  => $person->id,
        ];

        $this->postJson('/api/v1/users', $payload)
            ->assertStatus(422);
    }

    public function testDeveCadastrarUmUsuarioComSucesso()
    {
        $this->autenticarComPermissao('core.users.create');
        $person = Person::factory()->create();

        $payload = [
            'personId' => $person->id,
            'email'     => 'clark@dailyplanet.com',
            'password'  => 'Super_secure_password1',
        ];

        $response = $this->postJson('/api/v1/users', $payload)
            ->assertCreated()
            ->json();

        $this->assertDatabaseHas('core.users', [
            'email'    => 'clark@dailyplanet.com',
        ]);

        $this->assertDatabaseHas('core.user_companies', [
            'personId' => $person->id,
            'companyId' => currentCompany()->companyId,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE (Edição Administrativa do Usuário)
    |--------------------------------------------------------------------------
    */

    public function testDeveRetornarUnauthorizedAoAtualizarUsuarioSemAutenticacao()
    {
        $user = User::factory()->create();

        $this->putJson("/api/v1/users/{$user->id}", [])
            ->assertUnauthorized();
    }

    public function testDeveRetornarForbiddenAoAtualizarUsuarioSemPermissao()
    {
        $user = User::factory()->create();
        $this->autenticarSemPermissao();

        $this->putJson("/api/v1/users/{$user->id}", [])
            ->assertForbidden();
    }

    public function testDeveAtualizarDadosAdministrativosDoUsuarioComSucesso()
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->autenticarComPermissao('core.users.update', user:$user);

        $payload = [
            'email'     => $user->email,
            'password'  => 'Super_secure_password1',
            'status'    => 'inactive',
        ];

        $response = $this->patchJson("/api/v1/users/{$user->id}", $payload);

        $response->assertOk();

        $this->assertDatabaseHas('core.users', [
            'id'       => $user->id,
            'status'   => 'inactive',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE (Exclusão Administrativa de Usuário)
    |--------------------------------------------------------------------------
    */

    public function testDeveRetornarUnauthorizedAoRemoverUsuarioSemAutenticacao()
    {
        $user = User::factory()->create();

        $this->deleteJson("/api/v1/users/{$user->id}")
            ->assertUnauthorized();
    }

    public function testDeveRetornarForbiddenAoRemoverUsuarioSemPermissao()
    {
        $user = User::factory()->create();
        $this->autenticarSemPermissao(company:null, user:$user);

        $this->deleteJson("/api/v1/users/{$user->id}")
            ->assertForbidden();
    }

    public function testDeveRemoverUmUsuarioComSucesso()
    {
        $user = User::factory()->create();
        $this->autenticarComPermissao('core.users.delete', company:null, user:$user);

        $this->deleteJson("/api/v1/users/{$user->id}")
            ->assertNoContent();

        // Nota: Substitua por assertSoftDeleted se for implementar Soft Deletes na tabela core.users futura.
        $this->assertSoftDeleted('core.users', [
            'id' => $user->id,
        ]);
    }
}
