<?php
declare(strict_types=1);

namespace Mizam\Service;

use Exception;
use Mizam\Model\User;
use Mizam\Repo\UserRepo;
use PDO;

class UserService
{
    /**
     * @param int $user_id
     * @param PDO|null $pdo
     * @return User|null
     * @throws Exception
     */
    public static function getById(int $user_id, PDO $pdo = null): ?User
    {
        $repo = new UserRepo();
        return $repo->getById($user_id, $pdo);
    }

    /**
     * @param string $login_id
     * @return User|null
     * @throws Exception
     */
    public static function getByLoginId(string $login_id): ?User
    {
        $repo = new UserRepo();
        return $repo->getByLoginId($login_id);
    }

    /**
     * @param string $login_id
     * @param string $password
     * @return User|null
     * @throws Exception
     */
    public static function verifyAndGet(string $login_id, string $password): ?User
    {
        $repo = new UserRepo();
        $user = $repo->getByLoginId($login_id);

        if (is_null($user)) {
            return null;
        }

        if ($user->isValidPassword($password)) {
            return $user;
        } else {
            return null;
        }
    }

    /**
     * @param int $user_id
     * @return bool
     * @throws Exception
     */
    public static function isExistsId(int $user_id)
    {
        $repo = new UserRepo();
        $user = $repo->getById($user_id);

        return !is_null($user);
    }

    /**
     * @param string|null $login_id
     * @return bool
     * @throws Exception
     */
    public static function isExistsLoginId(?string $login_id)
    {
        $repo = new UserRepo();
        $user = $repo->getByLoginId($login_id);
        return !is_null($user);
    }

    /**
     * @param User $user
     * @param PDO|null $pdo
     * @return int
     * @throws Exception
     */
    public static function createWithUser(User $user, PDO $pdo = null): int
    {
        $repo = new UserRepo();
        return $repo->createWithUser($user, $pdo);
    }
}
