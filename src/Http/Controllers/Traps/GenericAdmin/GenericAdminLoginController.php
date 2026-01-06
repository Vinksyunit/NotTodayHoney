<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\GenericAdmin;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;

class GenericAdminLoginController
{
    use HandlesTrapBehavior;

    protected function getTrapName(): string
    {
        return 'generic_admin';
    }

    public function __invoke(Request $request): Response
    {
        return $this->executeTrap($request);
    }
}
