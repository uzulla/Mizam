<?php
declare(strict_types=1);

namespace Mizam\Web\Route;

use Exception;
use Mizam\Web\Traits\EmitterTrait;
use Mizam\Web\Session;
use Mizam\Web\Traits\TemplateEngineTrait;

class SignIn implements RouteInterface
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

        $session = new Session();
        $user = $session->signIn($login_id, $password);

        if (is_null($user)) {
            $params = ['errors' => ['login_failed' => 'ログインに失敗しました']];
            static::send(self::render('sign_in_form.twig', $params));
        } else {
            static::redirect('/');
        }
    }
}
