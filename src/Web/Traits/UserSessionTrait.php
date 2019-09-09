<?php
declare(strict_types=1);

namespace Mizam\Web\Traits;

use Exception;
use Mizam\Web\Model\UserSession;
use Mizam\Web\Service\UserSessionService;
use Mizam\Web\Session;

trait UserSessionTrait
{
    /**
     * @return UserSession|null
     * @throws Exception
     */
    public static function getUserSession(): ?UserSession
    {
        $session = new Session();
        $session->startOnceSession();

        return UserSessionService::getUserSession();
    }

    /**
     * @param $input_token
     * @return bool
     * @throws Exception
     */
    public static function validateCsrfToken($input_token = null): bool
    {
        if (is_null($input_token)) {
            $input_token = $_POST['csrf_token'] ?? null;
        }
        $us = static::getUserSession();
        return $input_token == $us->csrf_token;
    }
}
