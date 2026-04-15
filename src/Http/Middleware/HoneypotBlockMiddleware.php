<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Vinksyunit\NotTodayHoney\Services\AttackerDetectionService;

class HoneypotBlockMiddleware
{
    public function __construct(private readonly AttackerDetectionService $service) {}

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        $whitelist = config('not-today-honey.whitelist', []);
        if (in_array($ip, $whitelist, true)) {
            return $next($request);
        }

        if ($this->service->isBlocked($ip)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
