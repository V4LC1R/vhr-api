<?php

namespace Modules\Core\Tests\Feature;

use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\Person;
use Modules\Core\Models\User;
use Tests\DBTestCase;

class UserTest extends DBTestCase
{
    protected bool $seed = true;

    protected function autenticarComPermissao(string $permission): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo($permission);
        Sanctum::actingAs($user);

        return $user;
    }

    protected function autenticarSemPermissao(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        return $user;
    }

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
                'password',
                'personId',
            ]);
    }

    public function testDeveRetornarErroDeValidacaoSePersonIdNaoExistirNoBanco()
    {
        $this->autenticarComPermissao('core.users.create');

        $payload = [
            'email'     => 'clark@dailyplanet.com',
            'password'  => 'secret123',
            'personId'  => fake()->uuid(), // ID aleatório que não existe na tabela core.persons
        ];

        $this->postJson('/api/v1/users', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['personId']);
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
            'email'     => 'clark@dailyplanet.com',
            'password'  => 'Super_secure_password1',
            'personId'  => $person->id,
        ];

        $this->postJson('/api/v1/users', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('core.users', [
            'email'    => 'clark@dailyplanet.com',
            'personId' => $person->id,
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
        $this->autenticarComPermissao('core.users.update');

        $user = User::factory()->create(['status' => 'active']);

        $payload = [
            'email'     => $user->email,
            'personId'  => $user->personId,
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
        $this->autenticarSemPermissao();

        $this->deleteJson("/api/v1/users/{$user->id}")
            ->assertForbidden();
    }

    public function testDeveRemoverUmUsuarioComSucesso()
    {
        $this->autenticarComPermissao('core.users.delete');
        $user = User::factory()->create();

        $this->deleteJson("/api/v1/users/{$user->id}")
            ->assertNoContent();

        // Nota: Substitua por assertSoftDeleted se for implementar Soft Deletes na tabela core.users futura.
        $this->assertSoftDeleted('core.users', [
            'id' => $user->id,
        ]);
    }
}
