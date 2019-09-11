<?php
declare(strict_types=1);

namespace Mizam\Repo;

use Exception;
use Mizam\Env;
use Mizam\Model\User;
use PDO;
use RuntimeException;

class UserRepo extends DBBase
{
    /**
     * @param string $login_id
     * @return User|null
     * @throws Exception
     */
    public function getByLoginId(string $login_id): ?User
    {
        $pdo = $this->getPdo();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE login_id=:login_id");
        $stmt->bindValue('login_id', $login_id, PDO::PARAM_STR);
        $stmt->execute();

        /** @var User $user */
        $user = $stmt->fetchObject(User::class);

        return $user === false ? null : $user;
    }

    /**
     * @param int $id
     * @param PDO|null $pdo
     * @return User|null
     * @throws Exception
     */
    public function getById(int $id, PDO $pdo = null): ?User
    {
        if (is_null($pdo)) {
            $pdo = $this->getPdo();
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=:user_id");
        $stmt->bindValue('user_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        /** @var User $user */
        $user = $stmt->fetchObject(User::class);

        return $user === false ? null : $user;
    }

    /**
     * @param User $user
     * @param PDO|null $pdo
     * @return int
     * @throws Exception
     */
    public function createWithUser(User $user, PDO $pdo = null): int
    {
        if (is_null($pdo)) {
            $pdo = $this->getPdo();
        }

        $stmt = $pdo->prepare("
INSERT INTO users
(
login_id,
nick,
hashed_password,
updated_at,
created_at
) VALUES (
:login_id,
:nick,
:hashed_password,
:updated_at,
:created_at
)
         ");

        $stmt->bindValue('login_id', $user->login_id, PDO::PARAM_STR);
        $stmt->bindValue('nick', $user->nick, PDO::PARAM_STR);
        $stmt->bindValue('hashed_password', $user->hashed_password, PDO::PARAM_STR);
        $stmt->bindValue('updated_at', $user->updated_at, PDO::PARAM_INT);
        $stmt->bindValue('created_at', $user->created_at, PDO::PARAM_INT);

        $result = $stmt->execute();

        if (!$result) {
            throw new RuntimeException("insert failed.");
        }

        if(Env::getenv("DB_TYPE")=='heroku_pg'){
            $stmt = $pdo->prepare("select lastval() as lastval;");
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $id = (int)$res['lastval'];
        }else {
            $id = (int)$pdo->lastInsertId('id');
        }
        return $id;
    }
}
