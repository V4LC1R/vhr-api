<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
use Modules\Core\Models\User;
use Modules\Core\Models\UserCompany;
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
                'password',
                'role',
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
            'role'      => 'employee',
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
            'role'      => 'humanResource',
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

        $userCompany = UserCompany::query()
            ->where('personId', $person->id)
            ->where('companyId', currentCompany()->companyId)
            ->first();

        $this->assertTrue($userCompany->hasRole('humanResource'));
    }

    public function testDeveRetornarErroDeValidacaoQuandoRoleForInvalida()
    {
        $this->autenticarComPermissao('core.users.create');
        $person = Person::factory()->create();

        $payload = [
            'personId' => $person->id,
            'email'    => 'diana@themyscira.com',
            'password' => 'Super_secure_password1',
            'role'     => 'papel-inexistente',
        ];

        $this->postJson('/api/v1/users', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    /*
    |--------------------------------------------------------------------------
    | LIST (Listagem de Usuários da Empresa Ativa)
    |--------------------------------------------------------------------------
    */

    public function testDeveRetornarForbiddenAoListarUsuariosSemPermissao()
    {
        $this->autenticarSemPermissao();

        $this->getJson('/api/v1/users')
            ->assertForbidden();
    }

    public function testDeveListarApenasUsuariosDaEmpresaAtiva()
    {
        $company = Company::factory()->create();
        $user = $this->autenticarComPermissao('core.users.view', $company);
        $companyId = $company->id;

        $outroUser = User::factory()->create();
        UserCompany::factory()->create([
            'userId'    => $outroUser->id,
            'companyId' => $companyId,
        ]);

        $usuarioDeOutraEmpresa = User::factory()->create();
        UserCompany::factory()->create([
            'userId' => $usuarioDeOutraEmpresa->id,
        ]);

        $response = $this->getJson('/api/v1/users')
            ->assertOk()
            ->json();

        $ids = collect($response['data'])->pluck('id');

        $this->assertTrue($ids->contains($user->id));
        $this->assertTrue($ids->contains($outroUser->id));
        $this->assertFalse($ids->contains($usuarioDeOutraEmpresa->id));
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
