<?php
declare(strict_types=1);

namespace Mizam\Web\Route;

Interface RouteInterface
{
    public function __invoke(array $vars): void;
}
