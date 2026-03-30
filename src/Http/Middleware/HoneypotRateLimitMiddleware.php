<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Vinksyunit\NotTodayHoney\Events\TrapCampaignDetectedEvent;

class HoneypotRateLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $perIp = config('not-today-honey.rate_limiting.per_ip');

        if ($perIp['enabled']) {
            $key = 'honey_ip:'.$request->ip();
            $allowed = RateLimiter::attempt(
                $key,
                $perIp['max_hits'],
                fn () => true,
                $perIp['decay_minutes'] * 60
            );

            if (! $allowed) {
                return response('Too Many Requests', 429);
            }
        }

        $global = config('not-today-honey.rate_limiting.global');

        if ($global['enabled']) {
            $allowed = RateLimiter::attempt(
                'honey_global',
                $global['max_hits'],
                fn () => true,
                $global['decay_minutes'] * 60
            );

            if (! $allowed) {
                TrapCampaignDetectedEvent::dispatch($global['max_hits'], $global['decay_minutes']);

                return response('Too Many Requests', 429);
            }
        }

        return $next($request);
    }
}
