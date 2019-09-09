<?php
declare(strict_types=1);

namespace Mizam\Web\Route;

use Exception;
use Mizam\Repo\SignUpTokenRepo;
use Mizam\Service\SignUpTokenService;
use Mizam\Service\UserService;
use Mizam\Web\Traits\EmitterTrait;
use Mizam\Web\Traits\TemplateEngineTrait;

class SignUpVerify implements RouteInterface
{
    use TemplateEngineTrait;
    use EmitterTrait;

    /**
     * @param array $vars
     * @throws Exception
     */
    public function __invoke(array $vars): void
    {
        $token = $vars['token'] ?? "";

        if ($token === "") {
            static::send(self::render('sign_up_email_check_failed.twig'));
            return;
        }

        $pdo = SignUpTokenRepo::getNewPdo();

        try {
            $pdo->beginTransaction();

            $sign_up_token = SignUpTokenService::getByToken($token, $pdo);

            if (is_null($sign_up_token)) {
                static::send(self::render('sign_up_email_check_failed.twig'));
                return;
            }

            if ($sign_up_token->isExpired()) {
                static::send(self::render('sign_up_email_check_failed.twig'));
                return;
            }

            $user = SignUpTokenService::getNewUserBySignUpToken($sign_up_token);

            if (UserService::isExistsLoginId($user->login_id)) {
                static::send(self::render('sign_up_email_check_failed.twig'));
                return;
            }

            UserService::createWithUser($user, $pdo);
            SignUpTokenService::deleteWithSignUpToken($sign_up_token, $pdo);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

        static::send(self::render('sign_up_email_check_done.twig'));
    }
}
