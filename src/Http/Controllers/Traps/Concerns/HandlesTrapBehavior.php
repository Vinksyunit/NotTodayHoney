<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;
use Vinksyunit\NotTodayHoney\Enums\TrapBehavior;
use Vinksyunit\NotTodayHoney\Models\CredentialAttempt;
use Vinksyunit\NotTodayHoney\Models\TrapAttempt;
use Vinksyunit\NotTodayHoney\Services\AttackerDetectionService;

trait HandlesTrapBehavior
{
    /**
     * Get the trap configuration key (wordpress, phpmyadmin, generic_admin).
     */
    abstract protected function getTrapName(): string;

    /**
     * Get the alert level for this controller action.
     * Override in specific controllers for different levels.
     */
    protected function getAlertLevel(): AlertLevel
    {
        return AlertLevel::PROBING;
    }

    /**
     * Execute the trap behavior and log the attempt.
     */
    protected function executeTrap(Request $request): SymfonyResponse
    {
        $trapConfig = config("not-today-honey.traps.{$this->getTrapName()}");
        $behavior = $trapConfig['behavior'];

        $detection = $this->recordDetection($request);

        $this->logTrapAttempt($request, $detection->id);

        return $this->respondWithBehavior($behavior, $request);
    }

    /**
     * Execute the trap for login submission with credential checking.
     *
     * @param  string  $usernameField  The form field name for username
     * @param  string  $passwordField  The form field name for password
     */
    protected function executeLoginTrap(Request $request, string $usernameField, string $passwordField): SymfonyResponse
    {
        $username = $request->input($usernameField, '');
        $password = $request->input($passwordField, '');

        $credentialCheck = $this->checkCredentials($username, $password);

        $alertLevel = $credentialCheck['password_matched']
            ? AlertLevel::ATTACKING
            : AlertLevel::INTRUSION_ATTEMPT;

        $detection = app(AttackerDetectionService::class)
            ->recordAttempt($request->ip(), $alertLevel);

        $this->logTrapAttempt($request, $detection->id);

        $this->logCredentialAttempt(
            $detection->id,
            $username,
            $credentialCheck['credential_id'],
            $credentialCheck['username_matched'],
            $credentialCheck['password_matched']
        );

        if ($credentialCheck['password_matched']) {
            $trapConfig = config("not-today-honey.traps.{$this->getTrapName()}");

            return $this->respondWithBehavior($trapConfig['behavior'], $request);
        }

        return $this->respondLoginFailed($request, $username);
    }

    /**
     * Check if credentials match known leaked credentials.
     *
     * @return array{credential_id: string|null, username_matched: bool, password_matched: bool}
     */
    protected function checkCredentials(string $username, string $password): array
    {
        /** @var array<string> $knownUsernames */
        $knownUsernames = config('not-today-honey.credentials.usernames', []);
        /** @var array<array{id: string, hash: string}> $knownPasswords */
        $knownPasswords = config('not-today-honey.credentials.passwords', []);

        $usernameMatched = in_array($username, $knownUsernames, true);
        $passwordMatched = false;
        $credentialId = null;

        foreach ($knownPasswords as $credential) {
            if (Hash::check($password, $credential['hash'])) {
                $passwordMatched = true;
                $credentialId = $credential['id'];
                break;
            }
        }

        return [
            'credential_id' => $credentialId,
            'username_matched' => $usernameMatched,
            'password_matched' => $passwordMatched,
        ];
    }

    /**
     * Log credential attempt to database.
     */
    protected function logCredentialAttempt(
        int $detectionId,
        string $username,
        ?string $credentialId,
        bool $usernameMatched,
        bool $passwordMatched
    ): void {
        CredentialAttempt::create([
            'attacker_detection_id' => $detectionId,
            'credential_id' => $credentialId,
            'username_used' => $username,
            'username_matched' => $usernameMatched,
            'password_matched' => $passwordMatched,
            'created_at' => now(),
        ]);
    }

    /**
     * Return a realistic login failed response.
     * Override in specific controllers for trap-specific error pages.
     */
    protected function respondLoginFailed(Request $request, string $username): Response
    {
        return response('Login failed', 401);
    }

    /**
     * Log the trap attempt to database.
     */
    protected function logTrapAttempt(Request $request, int $detectionId): void
    {
        TrapAttempt::create([
            'attacker_detection_id' => $detectionId,
            'trap_name' => $this->getTrapName(),
            'path' => $request->path(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'created_at' => now(),
        ]);
    }

    /**
     * Record the detection via service.
     */
    protected function recordDetection(Request $request): \Vinksyunit\NotTodayHoney\Models\AttackerDetection
    {
        return app(AttackerDetectionService::class)
            ->recordAttempt($request->ip(), $this->getAlertLevel());
    }

    /**
     * Respond based on configured trap behavior.
     */
    protected function respondWithBehavior(TrapBehavior $behavior, Request $request): SymfonyResponse
    {
        return match ($behavior) {
            TrapBehavior::FORBIDDEN => $this->respondForbidden(),
            TrapBehavior::ERROR => $this->respondError(),
            TrapBehavior::INFINITE_LOADING => $this->respondInfiniteLoading(),
            TrapBehavior::FAKE_SUCCESS => $this->respondFakeSuccess($request),
        };
    }

    /**
     * Return 403 Forbidden response.
     */
    protected function respondForbidden(): Response
    {
        return response('Forbidden', 403);
    }

    /**
     * Return 500 Internal Server Error response.
     */
    protected function respondError(): Response
    {
        return response('Internal Server Error', 500);
    }

    /**
     * Return infinite loading response (tarpitting).
     */
    protected function respondInfiniteLoading(): StreamedResponse
    {
        return response()->stream(function (): void {
            while (true) {
                echo ' ';
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                sleep(5);

                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Return fake success response.
     * Override in specific controllers for realistic responses.
     */
    protected function respondFakeSuccess(Request $request): Response
    {
        return response('', 200);
    }
}
