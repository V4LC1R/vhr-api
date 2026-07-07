<?php

declare(strict_types=1);

namespace Modules\Auth\Mail;

use App\Supports\Mail\EmailBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $resetUrl,
        public int $expiresInMinutes,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recuperação de senha',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: EmailBuilder::make()
                ->preheader('Recupere o acesso à sua conta VHR.')
                ->heading('Recuperação de senha')
                ->paragraph('Recebemos uma solicitação para redefinir a senha da sua conta. Clique no botão abaixo para escolher uma nova senha.')
                ->button('Redefinir senha', $this->resetUrl)
                ->muted("Este link expira em {$this->expiresInMinutes} minutos. Se você não solicitou a redefinição, ignore este e-mail — sua senha continua a mesma.")
                ->divider()
                ->muted('Se o botão não funcionar, copie e cole este endereço no navegador:')
                ->urlText($this->resetUrl)
                ->toHtml(),
        );
    }
}
