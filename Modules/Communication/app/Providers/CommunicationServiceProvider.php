<?php

namespace Modules\Communication\Providers;

use Modules\Communication\Contracts\MailerInterface;
use Modules\Communication\Services\Mailers\LaravelMailer;
use Nwidart\Modules\Support\ModuleServiceProvider;

class CommunicationServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Communication';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'communication';

    /**
     * Módulo de consumo interno (sem rotas HTTP): expõe apenas o MailerInterface.
     *
     * @var string[]
     */
    protected array $providers = [];

    public function register(): void
    {
        parent::register();

        $this->app->bind(MailerInterface::class, LaravelMailer::class);
    }
}
