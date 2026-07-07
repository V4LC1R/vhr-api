<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use App\Contracts\PasswordResetTokenRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Data\ForgotPasswordData;
use Modules\Auth\Data\ResetPasswordData;
use Modules\Auth\Mail\PasswordResetMail;
use Modules\Communication\Contracts\MailerInterface;
use Modules\Core\Enums\TokenPasswordStatusEnum;

class PasswordResetService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected PasswordResetTokenRepositoryInterface $tokenRepository,
        protected MailerInterface $mailer,
    ) {
    }

    public function request(ForgotPasswordData $data): void
    {
        $user = $this->userRepository->findByEmail($data->email);

        // Anti-enumeração: e-mail inexistente não gera token nem e-mail.
        // O controller responde 200 genérico em qualquer caso.
        if (! $user) {
            return;
        }

        // Um token ativo por vez.
        $this->tokenRepository->invalidatePendingForUser($user->id);

        $plain = Str::random(64);
        $ttl   = (int) config('auth.token_ttl', 60);

        $this->tokenRepository->create([
            'userId'      => $user->id,
            'token'       => hash('sha256', $plain),
            'status'      => TokenPasswordStatusEnum::PENDING,
            'expiresAt'   => now()->addMinutes($ttl),
            'requestedAt' => now(),
        ]);

        // O token em claro só existe no link do e-mail (o DB guarda o sha256).
        $url = route('password.reset', ['token' => $plain]);

        $this->mailer->send($user->email, new PasswordResetMail($url, $ttl));
    }

    public function reset(ResetPasswordData $data): void
    {
        $record = $this->tokenRepository->findValidByToken(hash('sha256', $data->token));

        if (! $record) {
            throw ValidationException::withMessages([
                'token' => 'Token inválido ou expirado.',
            ]);
        }

        DB::transaction(function () use ($record, $data): void {
            // O cast 'hashed' do User aplica o hash da nova senha.
            $user = $this->userRepository->update($record->userId, [
                'password' => $data->password,
            ]);

            $this->tokenRepository->markUsed($record);
            $this->tokenRepository->invalidatePendingForUser($record->userId);

            // Revoga os PATs Sanctum do usuário.
            $user?->tokens()->delete();
        });
    }
}
