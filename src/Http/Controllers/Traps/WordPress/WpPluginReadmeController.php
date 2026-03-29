<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\WordPress;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;

class WpPluginReadmeController
{
    use HandlesTrapBehavior;

    protected function getTrapName(): string
    {
        return 'wordpress';
    }

    public function __invoke(Request $request, string $plugin): SymfonyResponse
    {
        /** @var array<string, string> $plugins */
        $plugins = config('not-today-honey.traps.wordpress.specific.fingerprint.plugins', []);

        if (! array_key_exists($plugin, $plugins)) {
            abort(404);
        }

        $version = $plugins[$plugin];
        $detection = $this->recordDetection($request);
        $this->logTrapAttempt($request, $detection->id);

        return $this->buildReadmeResponse($plugin, $version);
    }

    private function buildReadmeResponse(string $plugin, string $version): Response
    {
        $content = implode("\n", [
            sprintf('=== %s ===', $plugin),
            'Contributors: contributors',
            'Tags: security, wordpress',
            'Requires at least: 5.0',
            'Tested up to: 6.4',
            'Stable tag: '.$version,
            'License: GPLv2 or later',
            'License URI: https://www.gnu.org/licenses/gpl-2.0.html',
            '',
            '== Description ==',
            '',
            'A WordPress plugin.',
            '',
            '== Changelog ==',
            '',
            sprintf('= %s =', $version),
            '* Latest release.',
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
