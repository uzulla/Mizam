<?php
declare(strict_types=1);

namespace Mizam\Web\Service;

use Exception;
use Mizam\Log;
use Mizam\Model\User;
use Mizam\Service\UserService;
use Mizam\Web\Model\UserSession;
use Mizam\Web\Session;

trait UserSessionService
{
    /**
     * @return UserSession|null
     * @throws Exception
     */
    public static function getUserSession(): ?UserSession
    {
        $session = new Session();
        $session->startOnceSession();

        /** @var User $user */
        $user = $_SESSION['user'] ?? null;

        if (is_null($user) || !UserService::isExistsId($user->id)) {
            return null;
        }

        Log::debug("UserSession as {$user->nick}");

        $us = static::convertFromUserModel($user);

        if (!isset($_SESSION['csrf_token']) || strlen($_SESSION['csrf_token']) < 40) {
            $_SESSION['csrf_token'] = Session::generateCsrfToken();
        }

        $us->csrf_token = $_SESSION['csrf_token'];

        return $us;
    }

    private static function convertFromUserModel(User $user): UserSession
    {
        $us = new UserSession();
        $us->user_id = $user->id;
        $us->nick = $user->nick;
        return $us;
    }

}
