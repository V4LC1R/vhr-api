<?php

declare(strict_types=1);

namespace Modules\Communication\Services\Mailers;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Modules\Communication\Contracts\MailerInterface;

class LaravelMailer implements MailerInterface
{
    public function send(string $to, Mailable $mailable): void
    {
        Mail::to($to)->queue($mailable);
    }
}
