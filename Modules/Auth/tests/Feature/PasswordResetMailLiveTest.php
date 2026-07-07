<?php

namespace Modules\Auth\Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Modules\Auth\Mail\PasswordResetMail;
use Tests\TestCase;

/**
 * Teste de ENTREGA REAL (smoke) do e-mail de recuperação de senha via Resend.
 *
 * Não roda na suíte normal: só executa quando MAIL_LIVE_TEST estiver setado e
 * RESEND_API_KEY + DEV_EMAIL_TESTER existirem no .env. Ele ENVIA de verdade —
 * use conscientemente.
 *
 *   docker compose exec -u root app env MAIL_LIVE_TEST=1 \
 *       php artisan test --filter=PasswordResetMailLiveTest
 */
class PasswordResetMailLiveTest extends TestCase
{
    /** @test */
    public function testEnviaEmailRealDeRecuperacaoParaDevTester(): void
    {
        if (! env('MAIL_LIVE_TEST')) {
            $this->markTestSkipped(
                'Live mail test desativado. Rode com MAIL_LIVE_TEST=1 para enviar de verdade.'
            );
        }

        $to  = config('mail.dev_tester');
        $key = config('services.resend.key');

        if (! $to || ! $key) {
            $this->markTestSkipped(
                'Defina DEV_EMAIL_TESTER e RESEND_API_KEY no .env para rodar este teste.'
            );
        }

        $url = route('password.reset', ['token' => 'live-test-' . now()->timestamp]);

        // Mailer 'resend' explícito (o default em teste é 'array'/fake) e envio
        // síncrono. Se o Resend recusar (domínio não verificado etc.), lança e falha.
        $sent = Mail::mailer('resend')
            ->to($to)
            ->sendNow(new PasswordResetMail($url, 60));

        $this->assertNotNull($sent, "Falha ao enviar o e-mail de teste para {$to}.");
    }
}
