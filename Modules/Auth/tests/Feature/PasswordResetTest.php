<?php

namespace Modules\Auth\Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\Auth\Mail\PasswordResetMail;
use Modules\Core\Enums\TokenPasswordStatusEnum;
use Modules\Core\Models\PasswordResetToken;
use Modules\Core\Models\User;
use Tests\DBTestCase;

/**
 * Suíte de recuperação de senha (TDD).
 *
 * ATENÇÃO: esta é a especificação executável do fluxo e permanece VERMELHA
 * até a feature ser implementada. Falta construir:
 *   - módulo Communication (MailerInterface + LaravelMailer + bind);
 *   - Modules\Auth\Mail\PasswordResetMail (Mailable);
 *   - PasswordResetService (request/reset);
 *   - PasswordResetController + ForgotPasswordRequest/ResetPasswordRequest;
 *   - rotas auth.password.forgot / auth.password.reset em Modules/Auth/routes/api.php.
 *
 * Contrato dos endpoints:
 *   POST /api/auth/forgot-password  { email }
 *   POST /api/auth/reset-password   { token, password, password_confirmation }
 */
class PasswordResetTest extends DBTestCase
{
    private const FORGOT_URL = '/api/auth/forgot-password';
    private const RESET_URL  = '/api/auth/reset-password';

    private const MESSAGE_FORGOT = 'Se o e-mail existir, enviaremos as instruções.';
    private const MESSAGE_RESET  = 'Senha redefinida com sucesso.';

    private const SENHA_NOVA = 'novaSenhaForte123';

    /**
     * Cria um token pending com plaintext conhecido (a coluna guarda o sha256).
     */
    private function criarTokenPendente(User $user, string $plain): PasswordResetToken
    {
        return PasswordResetToken::factory()->create([
            'userId' => $user->id,
            'token'  => hash('sha256', $plain),
        ]);
    }

    /** @test */
    public function testForgotComEmailExistenteEnviaEmailECriaTokenPendente(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'existe@sistema.com']);

        $response = $this->postJson(self::FORGOT_URL, ['email' => 'existe@sistema.com']);

        $response->assertStatus(200)
            ->assertJson(['message' => self::MESSAGE_FORGOT]);

        Mail::assertQueued(
            PasswordResetMail::class,
            fn (PasswordResetMail $mail): bool => $mail->hasTo('existe@sistema.com')
        );

        $this->assertSame(
            1,
            PasswordResetToken::query()
                ->where('userId', $user->id)
                ->where('status', TokenPasswordStatusEnum::PENDING)
                ->count()
        );
    }

    /** @test */
    public function testForgotComEmailInexistenteNaoVazaExistencia(): void
    {
        Mail::fake();

        $response = $this->postJson(self::FORGOT_URL, ['email' => 'naoexiste@sistema.com']);

        // Mesma resposta do caso existente — anti-enumeração.
        $response->assertStatus(200)
            ->assertJson(['message' => self::MESSAGE_FORGOT]);

        Mail::assertNothingQueued();

        $this->assertSame(0, PasswordResetToken::query()->count());
    }

    /** @test */
    public function testResetComTokenValidoRedefineSenhaEMarcaComoUsado(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('senhaAntiga123'),
        ]);

        $token = $this->criarTokenPendente($user, 'plain-valido');
        // Um segundo token pending do mesmo usuário deve ser invalidado no reset.
        $outro = $this->criarTokenPendente($user, 'plain-outro');

        $response = $this->postJson(self::RESET_URL, [
            'token'                 => 'plain-valido',
            'password'              => self::SENHA_NOVA,
            'password_confirmation' => self::SENHA_NOVA,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => self::MESSAGE_RESET]);

        $this->assertTrue(Hash::check(self::SENHA_NOVA, $user->fresh()->password));

        $this->assertSame(TokenPasswordStatusEnum::USED, $token->fresh()->status);
        // Demais tokens pending do usuário não seguem válidos.
        $this->assertNotSame(TokenPasswordStatusEnum::PENDING, $outro->fresh()->status);
    }

    /** @test */
    public function testResetComTokenExpiradoRetorna422(): void
    {
        $user = User::factory()->create();

        PasswordResetToken::factory()->expired()->create([
            'userId' => $user->id,
            'token'  => hash('sha256', 'plain-expirado'),
        ]);

        $response = $this->postJson(self::RESET_URL, [
            'token'                 => 'plain-expirado',
            'password'              => self::SENHA_NOVA,
            'password_confirmation' => self::SENHA_NOVA,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('token');
    }

    /** @test */
    public function testResetComTokenJaUsadoRetorna422(): void
    {
        $user = User::factory()->create();

        PasswordResetToken::factory()->used()->create([
            'userId' => $user->id,
            'token'  => hash('sha256', 'plain-usado'),
        ]);

        $response = $this->postJson(self::RESET_URL, [
            'token'                 => 'plain-usado',
            'password'              => self::SENHA_NOVA,
            'password_confirmation' => self::SENHA_NOVA,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('token');
    }

    /** @test */
    public function testResetComTokenInexistenteRetorna422(): void
    {
        $response = $this->postJson(self::RESET_URL, [
            'token'                 => 'token-que-nao-existe',
            'password'              => self::SENHA_NOVA,
            'password_confirmation' => self::SENHA_NOVA,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('token');
    }

    /** @test */
    public function testResetComConfirmacaoDivergenteRetorna422(): void
    {
        $user = User::factory()->create();
        $this->criarTokenPendente($user, 'plain-divergente');

        $response = $this->postJson(self::RESET_URL, [
            'token'                 => 'plain-divergente',
            'password'              => self::SENHA_NOVA,
            'password_confirmation' => 'outraCoisa456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /** @test */
    public function testTokenEPersistidoComoHashNaoEmClaro(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'hash@sistema.com']);

        $this->postJson(self::FORGOT_URL, ['email' => 'hash@sistema.com'])
            ->assertStatus(200);

        $registro = PasswordResetToken::query()
            ->where('userId', $user->id)
            ->firstOrFail();

        // A coluna guarda um sha256 (64 hex), nunca o valor em claro.
        $this->assertSame(64, strlen($registro->token));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $registro->token);
    }

    /** @test */
    public function testResetRevogaTokensSanctumDoUsuario(): void
    {
        $user = User::factory()->create();
        $user->createToken('dispositivo-antigo');

        $this->assertSame(1, $user->tokens()->count());

        $this->criarTokenPendente($user, 'plain-sanctum');

        $this->postJson(self::RESET_URL, [
            'token'                 => 'plain-sanctum',
            'password'              => self::SENHA_NOVA,
            'password_confirmation' => self::SENHA_NOVA,
        ])->assertStatus(200);

        $this->assertSame(0, $user->fresh()->tokens()->count());
    }
}
