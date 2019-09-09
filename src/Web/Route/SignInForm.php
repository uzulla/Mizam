<?php
declare(strict_types=1);

namespace Mizam\Web\Route;

use Exception;
use Mizam\Web\Traits\EmitterTrait;
use Mizam\Web\Traits\TemplateEngineTrait;

class SignInForm implements RouteInterface
{
    use TemplateEngineTrait;
    use EmitterTrait;

    /**
     * @param array $vars
     * @throws Exception
     */
    public function __invoke(array $vars): void
    {
        static::send(self::render('sign_in_form.twig'));
    }
}
