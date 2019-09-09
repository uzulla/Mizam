<?php
declare(strict_types=1);

namespace Mizam\Service;

use Exception;
use Mizam\Log;
use Mizam\Model\SignUpToken;
use Mizam\Model\User;
use Mizam\Repo\SignUpTokenRepo;
use Mizam\Traits\EmailSendTrait;
use Mizam\Traits\EmailTemplateTrait;
use PDO;

class SignUpTokenService
{
    use EmailTemplateTrait;
    use EmailSendTrait;

    /**
     * @param User $user
     * @throws Exception
     */
    public static function sendSignUpTokenMail(User $user): void
    {
        $sign_up_token = new SignUpToken();
        $sign_up_token->fillFromUser($user);

        $sut_repo = new SignUptokenRepo();
        $sut_repo->create($sign_up_token);

        [$subject, $body] = static::renderMail(
            'sign_up_verify.twig',
            [
                'nick' => $sign_up_token->user_nick,
                'token' => $sign_up_token->token,
                'verify_url' => getenv("SITE_URL") . "sign/up/verify/{$sign_up_token->token}"
            ]
        );

        Log::event("send sign up mail to {$sign_up_token->user_login_id}");

        static::sendEmail(
            $subject,
            $body,
            [getenv("MAIL_FROM") => "img uploader admin"],
            [$sign_up_token->user_login_id => $sign_up_token->user_nick]
        );
    }

    /**
     * @param string $token
     * @param PDO|null $pdo
     * @return SignUpToken
     * @throws Exception
     */
    public static function getByToken(string $token, PDO $pdo = null): ?SignUpToken
    {
        $sut_repo = new SignUpTokenRepo();
        return $sut_repo->getByToken($token, $pdo);
    }

    public static function getNewUserBySignUpToken(SignUpToken $sign_up_token): User
    {
        $user = new User();
        $user->login_id = $sign_up_token->user_login_id;
        $user->nick = $sign_up_token->user_nick;
        $user->hashed_password = $sign_up_token->user_hashed_password;
        $user->created_at = $sign_up_token->user_created_at;
        $user->updated_at = $sign_up_token->user_updated_at;
        return $user;
    }

    /**
     * @param SignUpToken $sign_up_token
     * @param PDO|null $pdo
     * @return int
     * @throws Exception
     */
    public static function deleteWithSignUpToken(SignUpToken $sign_up_token, PDO $pdo = null): int
    {
        $repo = new SignUpTokenRepo();
        return $repo->deleteWithSignUpToken($sign_up_token, $pdo);
    }

    /**
     * @return int delete row num
     * @throws Exception
     */
    public static function deleteExpireRecords(): int
    {
        $repo = new SignUpTokenRepo();
        return $repo->deleteExpireRecords();
    }
}
