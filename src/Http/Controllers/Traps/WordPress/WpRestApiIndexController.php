<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\WordPress;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HasHttpFingerprint;

class WpRestApiIndexController
{
    use HandlesTrapBehavior;
    use HasHttpFingerprint;

    protected function getTrapName(): string
    {
        return 'wordpress';
    }

    public function __invoke(Request $request): SymfonyResponse
    {
        $detection = $this->recordDetection($request);
        $this->logTrapAttempt($request, $detection->id);

        return $this->applyFingerprint($request, $this->buildDiscoveryResponse($request));
    }

    protected function buildDiscoveryResponse(Request $request): JsonResponse
    {
        $host = $request->getSchemeAndHttpHost();

        return response()->json([
            'name' => config('not-today-honey.traps.wordpress.specific.site_name', 'WordPress'),
            'description' => '',
            'url' => $host,
            'home' => $host,
            'gmt_offset' => 0,
            'timezone_string' => '',
            'namespaces' => ['oembed/1.0', 'wp/v2'],
            'authentication' => [],
            'routes' => [],
        ]);
    }
}
