<?php

declare(strict_types=1);

namespace App\Supports\Mail;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

/**
 * Builder fluente para e-mails transacionais com a identidade VHR.
 *
 * Acumula blocos de conteúdo e os renderiza dentro do layout de marca
 * (`resources/views/emails/layout.blade.php`). Toda a estilização vive no
 * layout; aqui só descrevemos o conteúdo.
 *
 * Exemplo:
 *   EmailBuilder::make()
 *       ->preheader('Recupere o acesso à sua conta VHR.')
 *       ->heading('Recuperação de senha')
 *       ->paragraph('Recebemos uma solicitação para redefinir a sua senha.')
 *       ->button('Redefinir senha', $url)
 *       ->muted('Este link expira em 60 minutos.')
 *       ->toHtml();
 */
class EmailBuilder implements Htmlable
{
    private string $preheader = '';

    /** @var list<array<string, string>> */
    private array $blocks = [];

    public static function make(): self
    {
        return new self();
    }

    /**
     * Texto de pré-visualização (oculto no corpo, exibido na lista do cliente).
     */
    public function preheader(string $text): self
    {
        $this->preheader = $text;

        return $this;
    }

    public function heading(string $text): self
    {
        $this->blocks[] = ['type' => 'heading', 'text' => $text];

        return $this;
    }

    public function paragraph(string $text): self
    {
        $this->blocks[] = ['type' => 'paragraph', 'text' => $text];

        return $this;
    }

    public function button(string $label, string $url): self
    {
        $this->blocks[] = ['type' => 'button', 'label' => $label, 'url' => $url];

        return $this;
    }

    /**
     * Parágrafo secundário (fine print).
     */
    public function muted(string $text): self
    {
        $this->blocks[] = ['type' => 'muted', 'text' => $text];

        return $this;
    }

    public function divider(): self
    {
        $this->blocks[] = ['type' => 'divider'];

        return $this;
    }

    /**
     * URL em claro (fallback de "copie e cole"), destacada em ouro e quebrável.
     */
    public function urlText(string $url): self
    {
        $this->blocks[] = ['type' => 'urlText', 'url' => $url];

        return $this;
    }

    public function render(): HtmlString
    {
        return new HtmlString(
            view('emails.layout', [
                'preheader' => $this->preheader,
                'blocks'    => $this->blocks,
            ])->render()
        );
    }

    public function toHtml(): string
    {
        return $this->render()->toHtml();
    }
}
