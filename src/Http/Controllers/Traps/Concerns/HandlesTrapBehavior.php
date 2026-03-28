<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;
use Vinksyunit\NotTodayHoney\Enums\TrapBehavior;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;
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
    protected function recordDetection(Request $request): AttackerDetection
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
