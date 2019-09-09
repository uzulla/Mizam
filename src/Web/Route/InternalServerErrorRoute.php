<?php
declare(strict_types=1);

namespace Mizam\Web\Route;


use Exception;
use Mizam\Web\Traits\EmitterTrait;

class InternalServerErrorRoute implements RouteInterface
{
    use EmitterTrait;

    /**
     * @param array $vars
     * @throws Exception
     */
    public function __invoke(array $vars): void
    {
        static::send("<h1>Internal Server Error</h1>", [], [], 500);
    }
}
