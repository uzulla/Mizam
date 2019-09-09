<?php
declare(strict_types=1);

namespace Mizam\Web;

use Exception;
use Mizam\Log;
use Mizam\Model\User;
use Mizam\Service\Random;
use Mizam\Service\UserService;
use Predis\Client;
use Predis\Session\Handler;
use RuntimeException;

class Session
{
    public function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public static function configureSessionHandler()
    {
        $redis_url = getenv("REDIS_URL"); // Heroku Redisを想定しています
        if ($redis_url !== false) {
            // 環境互換性の為にPure PHPな Predis のsession handlerをつかっていますが、
            // 可能なら ext-redis 等のC拡張セッションハンドラの方が速度が出ます
            $parsed_redis_url = parse_url($redis_url);

            $client = new Client(
                [
                    'scheme' => 'tcp',
                    'host' => $parsed_redis_url['host'],
                    'port' => $parsed_redis_url['port'],
                    'password' => $parsed_redis_url['pass']
                ],
                ['prefix' => 'sessions:']
            );

            $handler = new Handler($client);
            $handler->register();

            Log::debug("session handler is redis" . ini_get("session.save_handler"));
        }
    }

    /**
     * @param string $login_id
     * @param string $pass
     * @return User|null
     * @throws Exception
     */
    public function signIn(string $login_id, string $pass): ?User
    {
        $this->startOnceSession();
        session_unset();
        session_regenerate_id();

        $user = UserService::verifyAndGet($login_id, $pass);

        if (!($user instanceof User)) {
            Log::event("sign in failed {$login_id}");
            return null;
        }

        Log::event("sign in as {$user->nick} ({$user->id})");
        $_SESSION['user'] = $user;
        $_SESSION['csrf_token'] = static::generateCsrfToken();

        return $user;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function generateCsrfToken(): string
    {
        return Random::generateLongToken();
    }

    public function signOut()
    {
        $this->discardSession();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function signInStatus(): bool
    {
        $this->startOnceSession();

        if (!isset($_SESSION['user'])) {
            return false;
        }

        $user = $_SESSION['user'];
        if (!($user instanceof User)) {
            return false;
        }

        return UserService::isExistsId($user->id);
    }

    private function discardSession(): void
    {
        $this->startOnceSession();
        session_unset();

        if (ini_get("session.use_cookies")) { // Todo use Cookie class
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                0,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    public function startOnceSession(): void
    {
        $session_status = session_status();
        if ($session_status === PHP_SESSION_DISABLED) {
            throw new RuntimeException("the app require session function.");
        }

        if ($session_status === PHP_SESSION_ACTIVE) {
            return;
        }

        $result = session_start();
        if (!$result) {
            throw new RuntimeException("failed to start session.");
        }
    }
}
