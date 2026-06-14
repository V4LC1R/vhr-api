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

        $response = $this->postJson('/api/auth/login', [
            'email' => 'pai@sistema.com',
            'password' => $senha,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'usuario' => ['id', 'email']
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
        $usuario = User::factory()->create([
            'email' => 'pai@sistema.com',
        ]);

        Sanctum::actingAs($usuario, ['*'], 'web');

        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'usuario' => [
                    'id' => $usuario->id,
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

        $this->assertGuest('web');
    }

    /** @test */
    public function testUsuarioNaoAutenticadoEBarradoPelaProtecaoDeRota()
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }
}
