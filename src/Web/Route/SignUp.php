<?php
declare(strict_types=1);

namespace Mizam\Web\Route;

use Exception;
use Mizam\Model\User;
use Mizam\Service\SignUpTokenService;
use Mizam\Service\UserService;
use Mizam\Web\Traits\EmitterTrait;
use Mizam\Web\Traits\TemplateEngineTrait;

class SignUp implements RouteInterface
{
    use TemplateEngineTrait;
    use EmitterTrait;

    /**
     * @param array $vars
     * @throws Exception
     */
    public function __invoke(array $vars): void
    {
        $login_id = (string)$_POST['login_id'] ?? null;
        $password = (string)$_POST['password'] ?? null;
        $nick = (string)$_POST['nick'] ?? null;


        // validate
        $error_list = [];
        if (!filter_var($login_id, FILTER_VALIDATE_EMAIL)) {
            $error_list['login_id'] = 'required, as Email.';
        } elseif (UserService::isExistsLoginId($login_id)) {
            $error_list['login_id'] = 'this is exists email address.';
        }

        if (!preg_match("/.{8,}/u", $password)) {
            $error_list['password'] = 'required, more than 8 chars.';
        }

        if (!preg_match("/.{4,}/u", $nick)) {
            $error_list['nick'] = 'required, more than 4 chars.';
        }

        if (count($error_list) > 0) {
            static::send(self::render(
                'sign_up_form.twig',
                [
                    'errors' => $error_list,
                    'login_id' => $login_id,
                    'password' => $password,
                    'nick' => $nick
                ]
            ));
            return;
        }

        $user = new User();
        $user->login_id = $login_id;
        $user->nick = $nick;
        $user->updatePasswordHash($password);
        $user->created_at = time();
        $user->updated_at = $user->created_at;

        SignUpTokenService::sendSignUpTokenMail($user);

        static::send(self::render('sign_up_form_done.twig'));
    }
}
