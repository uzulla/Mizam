<?php
declare(strict_types=1);

namespace Mizam\Repo;

use Exception;
use Mizam\Model\SignUpToken;
use Mizam\Model\User;
use Mizam\Service\UserService;
use PDO;
use RuntimeException;

class SignUpTokenRepo extends DBBase
{

    /**
     * @param string $token
     * @param PDO|null $pdo
     * @return User|null
     * @throws Exception
     */
    public function getByToken(string $token, PDO $pdo = null): ?SignUpToken
    {
        if (is_null($pdo)) {
            $pdo = $this->getNewPdo();
        }

        $stmt = $pdo->prepare("SELECT * FROM sign_up_tokens WHERE token=:token");
        $stmt->bindValue('token', $token, PDO::PARAM_STR);
        $stmt->execute();

        $sut = $stmt->fetchObject(SignUpToken::class);
        return $sut === false ? null : $sut;
    }

    /**
     * @param string $token
     * @return User|null
     * @throws Exception
     */
    public function getUserByToken(string $token): ?User
    {
        $pdo = $this->getNewPdo();

        $stmt = $pdo->prepare("SELECT * FROM sign_up_tokens WHERE token=:token");
        $stmt->bindValue('token', $token, PDO::PARAM_STR);
        $stmt->execute();

        $sut = $stmt->fetch(PDO::FETCH_ASSOC);

        $user = new User();
        $user->login_id = $sut['user_login_id'];
        $user->hashed_password = $sut['user_hashed_password'];
        $user->nick = $sut['user_nick'];
        $user->created_at = $sut['created_at'];
        $user->updated_at = $sut['updated_at'];

        $id = UserService::createWithUser($user, $pdo);
        $user = UserService::getById($id, $pdo);

        $pdo->commit();

        return $user === false ? null : $user;
    }

    /**
     * @param SignUpToken $sut
     * @param PDO|null $pdo
     * @return int
     * @throws Exception
     */
    public function create(SignUpToken $sut, PDO $pdo = null): int
    {
        if (is_null($pdo)) {
            $pdo = $this->getPdo();
        }

        $stmt = $pdo->prepare("
INSERT INTO sign_up_tokens
(
token,
expire_at,
user_login_id,
user_nick,
user_hashed_password,
user_updated_at,
user_created_at
) VALUES (
:token,
:expire_at,
:user_login_id,
:user_nick,
:user_hashed_password,
:user_updated_at,
:user_created_at
)
         ");

        $stmt->bindValue('token', $sut->token, PDO::PARAM_STR);
        $stmt->bindValue('expire_at', $sut->expire_at, PDO::PARAM_INT);
        $stmt->bindValue('user_login_id', $sut->user_login_id, PDO::PARAM_STR);
        $stmt->bindValue('user_nick', $sut->user_nick, PDO::PARAM_STR);
        $stmt->bindValue('user_hashed_password', $sut->user_hashed_password, PDO::PARAM_STR);
        $stmt->bindValue('user_updated_at', $sut->user_updated_at, PDO::PARAM_INT);
        $stmt->bindValue('user_created_at', $sut->user_created_at, PDO::PARAM_INT);

        $result = $stmt->execute();

        if (!$result) {
            throw new RuntimeException("insert failed.");
        }

        if(getenv("DB_TYPE")=='heroku_pg'){
            $stmt = $pdo->prepare("select lastval() as lastval;");
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $id = (int)$res['lastval'];
        }else {
            $id = (int)$pdo->lastInsertId('id');
        }
        return $id;
    }

    /**
     * @param SignUpToken $sign_up_token
     * @param PDO|null $pdo
     * @return int
     * @throws Exception
     */
    public function deleteWithSignUpToken(SignUpToken $sign_up_token, PDO $pdo = null): int
    {
        if (is_null($pdo)) {
            $pdo = $this->getPdo();
        }

        $stmt = $pdo->prepare("DELETE FROM sign_up_tokens where id=:id");
        $stmt->bindValue('id', $sign_up_token->id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            new RuntimeException("delete failed");
        }

        $row_num = $stmt->rowCount();
        return $row_num;
    }

    /**
     * @return int delete row num
     * @throws Exception
     */
    public function deleteExpireRecords(): int
    {
        $pdo = $this->getPdo();

        $stmt = $pdo->prepare("DELETE FROM sign_up_tokens where expire_at<:time");
        $stmt->bindValue('time', time(), PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            new RuntimeException("delete failed");
        }

        $row_num = $stmt->rowCount();
        return $row_num;
    }
}
