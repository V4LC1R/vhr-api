<?php

namespace Modules\Core\Tests\Feature;

use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\User;
use Modules\Core\Models\Company;
use Tests\DBTestCase;

class CompanyTest extends DBTestCase
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

    // ==========================================
    // 1. TESTES DE CRIAÇÃO (CREATE)
    // ==========================================

    public function testUsuarioComPermissaoPodeCriarEmpresa(): void
    {
        $this->autenticarComPermissao('core.companies.create');

        $payload = [
            'name' => 'Empresa Sucesso LTDA',
            'cnpj' => '12345678000100',
        ];

        $response = $this->postJson('/api/v1/companies', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('core.companies', ['cnpj' => '12345678000100']);
    }

    public function testUsuarioSemPermissaoNaoPodeCriarEmpresa(): void
    {
        $this->autenticarSemPermissao();

        $payload = [
            'name' => 'Empresa Invasora LTDA',
            'cnpj' => '98765432000199',
        ];

        $response = $this->postJson('/api/v1/companies', $payload);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('core.companies', ['cnpj' => '98765432000199']);
    }

    // ==========================================
    // 2. TESTES DE EDIÇÃO (UPDATE)
    // ==========================================

    public function testUsuarioComPermissaoPodeAtualizarEmpresa(): void
    {
        $this->autenticarComPermissao('core.companies.update');
        $company = Company::factory()->create(['name' => 'Nome Antigo']);

        $payload = ['name' => 'Nome Atualizado','cnpj' => '98765432000199'];

        $response = $this->putJson("/api/v1/companies/{$company->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('core.companies', [
            'id' => $company->id,
            'name' => 'Nome Atualizado'
        ]);
    }

    public function testUsuarioSemPermissaoNaoPodeAtualizarEmpresa(): void
    {
        $this->autenticarSemPermissao();
        $company = Company::factory()->create(['name' => 'Nome Original',]);

        $payload = ['name' => 'Tentativa de Hack','cnpj' => '98765432000199'];

        $response = $this->putJson("/api/v1/companies/{$company->id}", $payload);

        $response->assertStatus(403);
        $this->assertDatabaseHas('core.companies', [
            'id' => $company->id,
            'name' => 'Nome Original'
        ]);
    }

    // ==========================================
    // 3. TESTES DE VISUALIZAÇÃO (VIEW)
    // ==========================================

    public function testUsuarioComPermissaoPodeListarEVerEmpresa(): void
    {
        $this->autenticarComPermissao('core.companies.view');
        $company = Company::factory()->create();

        // Testando o Show (ver uma específica)
        $responseShow = $this->getJson("/api/v1/companies/{$company->id}");
        $responseShow->assertStatus(200);

        // Testando o Index (listagem)
        $responseIndex = $this->getJson('/api/v1/companies');
        $responseIndex->assertStatus(200);
    }

    public function testUsuarioSemPermissaoNaoPodeVerEmpresa(): void
    {
        $this->autenticarSemPermissao();
        $company = Company::factory()->create();

        $response = $this->getJson("/api/v1/companies/{$company->id}");
        $response->assertStatus(403);

        $responseList = $this->getJson('/api/v1/companies');
        $responseList->assertStatus(403);
    }

    // ==========================================
    // 4. TESTES DE EXCLUSÃO (DELETE)
    // ==========================================

    public function testUsuarioComPermissaoPodeDeletarEmpresa(): void
    {
        $this->autenticarComPermissao('core.companies.delete');
        $company = Company::factory()->create();

        $response = $this->deleteJson("/api/v1/companies/{$company->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted($company);
    }

    public function testUsuarioSemPermissaoNaoPodeDeletarEmpresa(): void
    {
        $this->autenticarSemPermissao();
        $company = Company::factory()->create();

        $response = $this->deleteJson("/api/v1/companies/{$company->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('core.companies', ['id' => $company->id]);
    }
}
