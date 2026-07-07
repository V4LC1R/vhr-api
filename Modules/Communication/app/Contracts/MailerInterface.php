<?php

declare(strict_types=1);

namespace Modules\Communication\Contracts;

use Illuminate\Mail\Mailable;

/**
 * Contrato público do módulo Communication. Outros módulos dependem desta
 * interface (nunca de `Mail::` direto) para disparar e-mails.
 */
interface MailerInterface
{
    public function send(string $to, Mailable $mailable): void;
}
