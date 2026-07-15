<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Models\Company;
use Modules\Core\Models\UserCompany;
use Tests\DBTestCase;

class CompanyTest extends DBTestCase
{
    protected bool $seed = true;

    // ==========================================
    // 1. TESTES DE CRIAÇÃO (CREATE)
    // ==========================================

    public function testUsuarioComPermissaoPodeCriarEmpresa(): void
    {
        $user = $this->autenticarComRole('owner');

        $payload = [
            'name' => 'Empresa Sucesso LTDA',
            'cnpj' => '12345678000100',
        ];

        $response = $this->postJson('/api/v1/companies', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('core.companies', ['cnpj' => '12345678000100']);

        $novaEmpresa = Company::query()->where('cnpj', '12345678000100')->firstOrFail();

        $vinculo = UserCompany::query()
            ->where('userId', $user->id)
            ->where('companyId', $novaEmpresa->id)
            ->first();

        $this->assertNotNull($vinculo, 'O criador deveria ficar vinculado à nova empresa.');
        $this->assertTrue($vinculo->hasRole('owner'));
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
        $company = Company::factory()->create(['name' => 'Nome Antigo']);

        $this->autenticarComRole('owner', $company);

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
        $company = Company::factory()->create(['name' => 'Nome Original',]);

        $this->autenticarSemPermissao($company);

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
        $company = Company::factory()->create();

        $this->autenticarComPermissao('core.companies.view', $company);

        // Testando o Show (ver uma específica)
        $responseShow = $this->getJson("/api/v1/companies/{$company->id}");
        $responseShow->assertStatus(200);

        // Testando o Index (listagem)
        $responseIndex = $this->getJson('/api/v1/companies');
        $responseIndex->assertStatus(200);
    }

    public function testUsuarioSemPermissaoNaoPodeVerEmpresa(): void
    {
        $company = Company::factory()->create();
        $this->autenticarSemPermissao($company);

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
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', $company);

        $response = $this->deleteJson("/api/v1/companies/{$company->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted($company);
    }

    public function testUsuarioSemPermissaoNaoPodeDeletarEmpresa(): void
    {
        $company = Company::factory()->create();
        $this->autenticarSemPermissao($company);

        $response = $this->deleteJson("/api/v1/companies/{$company->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('core.companies', ['id' => $company->id]);
    }
}
