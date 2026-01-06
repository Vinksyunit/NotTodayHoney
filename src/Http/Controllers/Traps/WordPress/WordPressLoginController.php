<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\WordPress;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;

class WordPressLoginController
{
    use HandlesTrapBehavior;

    protected function getTrapName(): string
    {
        return 'wordpress';
    }

    public function __invoke(Request $request): Response
    {
        return $this->executeTrap($request);
    }
}
