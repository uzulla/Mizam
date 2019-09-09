<?php
declare(strict_types=1);

namespace Mizam\Web\Model;

use Exception;
use Mizam\Model\User;
use Mizam\Service\UserService;

class UserSession
{
    public $user_id;
    public $nick;
    public $csrf_token = null;

    /**
     * @return User
     * @throws Exception
     */
    public function getUser(): User
    {
        return UserService::getById($this->user_id);
    }
}
