<?php

namespace Modules\Auth\Tests\Feature;

use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\DBTestCase;
use Illuminate\Support\Facades\Hash;

class AuthTest extends DBTestCase
{
    /** @test */
    public function testUsuarioPodeLogarComCredenciaisCorretas()
    {
        $senha = 'senhaDoPai123';
        $usuario = User::factory()->create([
            'email' => 'pai@sistema.com',
            'password' => Hash::make($senha),
        ]);

        // No modo HttpOnly, o login dispara um POST e o Laravel anexa o cookie na resposta
        $response = $this->postJson('/api/auth/login', [
            'email' => 'pai@sistema.com',
            'password' => $senha,
        ]);

        // O status deve ser 200 OK e retornar os dados do usuário em PT-BR
        $response->assertStatus(200)
            ->assertJsonStructure([
                'usuario' => ['id', 'username', 'email']
            ]);

        $this->assertAuthenticatedAs($usuario, 'web');
    }

    /** @test */
    public function testUsuarioNaoPodeLogarComSenhaIncorreta()
    {
        $usuario = User::factory()->create([
            'email' => 'pai@sistema.com',
            'password' => Hash::make('senhaCorreta'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'pai@sistema.com',
            'password' => 'senhaErrada',
        ]);

        // Retorna não autorizado (401) e mantém o estado como visitante (guest)
        $response->assertStatus(401);
        $this->assertGuest('web');
    }

    /** @test */
    public function testUsuarioAutenticadoPodeAcessarSeuProprioPerfil()
    {
        // Certifique-se de NÃO passar 'name' aqui, passe apenas 'username'
        $usuario = User::factory()->create([
            'username' => 'gestorpai',
            'email' => 'pai@sistema.com',
        ]);

        // Simula o navegador enviando o Cookie HttpOnly válido (Guard web ativo)
        Sanctum::actingAs($usuario, ['*'], 'web');

        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'usuario' => [
                    'id' => $usuario->id,
                    'username' => 'gestorpai', // Valida o username correto no retorno
                    'email' => 'pai@sistema.com',
                ]
            ]);
    }

    /** @test */
    public function testUsuarioAutenticadoPodeFazerLogout()
    {
        $usuario = User::factory()->create();

        Sanctum::actingAs($usuario, ['*'], 'web');

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'mensagem' => 'Logout realizado com sucesso'
            ]);

        // Garante que a sessão foi destruída
        $this->assertGuest('web');
    }

    /** @test */
    public function testUsuarioNaoAutenticadoEBarradoPelaProtecaoDeRota()
    {
        // Tenta acessar o perfil sem cookies de sessão
        $response = $this->getJson('/api/auth/me');

        // Como configuramos o bootstrap/app.php para forçar JSON, o Laravel 13 devolve 401 automaticamente
        $response->assertStatus(401);
    }
}
