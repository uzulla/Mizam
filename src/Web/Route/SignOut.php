<?php
declare(strict_types=1);

namespace Mizam\Web\Route;

use Exception;
use Mizam\Web\Traits\EmitterTrait;
use Mizam\Web\Session;
use Mizam\Web\Traits\UserSessionTrait;

class SignOut implements RouteInterface
{
    use EmitterTrait;
    use UserSessionTrait;

    /**
     * @param array $vars
     * @throws Exception
     */
    public function __invoke(array $vars): void
    {
        if (!static::validateCsrfToken()) {
            (new BadCsrfTokenRequest())([]);
            return;
        }

        $session = new Session();
        $session->signOut();

        static::redirect('/');
    }
}
