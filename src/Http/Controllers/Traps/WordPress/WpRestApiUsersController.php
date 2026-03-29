<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\WordPress;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HasHttpFingerprint;

class WpRestApiUsersController
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

        return $this->applyFingerprint($request, $this->buildUsersResponse($request));
    }

    protected function buildUsersResponse(Request $request): JsonResponse
    {
        $host = $request->getSchemeAndHttpHost();
        /** @var array<string> $fakeUsers */
        $fakeUsers = config('not-today-honey.traps.wordpress.specific.fingerprint.fake_users', ['admin']);

        $users = [];
        foreach (array_values($fakeUsers) as $index => $slug) {
            $id = $index + 1;
            $users[] = [
                'id' => $id,
                'name' => $slug,
                'url' => '',
                'description' => '',
                'link' => "{$host}/?author={$id}",
                'slug' => $slug,
                'avatar_urls' => [
                    '24' => 'https://secure.gravatar.com/avatar/?s=24&d=mm&r=g',
                    '48' => 'https://secure.gravatar.com/avatar/?s=48&d=mm&r=g',
                    '96' => 'https://secure.gravatar.com/avatar/?s=96&d=mm&r=g',
                ],
                '_links' => [
                    'self' => [['href' => "{$host}/wp-json/wp/v2/users/{$id}"]],
                    'collection' => [['href' => "{$host}/wp-json/wp/v2/users"]],
                ],
            ];
        }

        return response()->json($users);
    }
}
