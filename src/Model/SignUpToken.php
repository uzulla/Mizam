<?php
declare(strict_types=1);

namespace Mizam\Model;

use Exception;
use Mizam\Service\Random;

class SignUpToken
{
    public $id = 0;
    public $token = null;
    public $expire_at = null;
    public $user_login_id;
    public $user_hashed_password;
    public $user_nick;
    public $user_updated_at;
    public $user_created_at;

    /**
     * SignUpToken constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->fillRandTokenAndExpireAt();

        if ($this->id == 0) return;

        $this->id = (int)$this->id;
        $this->expire_at = (int)$this->expire_at;
    }

    public function fillFromUser(User $user): void
    {
        $this->user_login_id = $user->login_id;
        $this->user_hashed_password = $user->hashed_password;
        $this->user_nick = $user->nick;
        $this->user_updated_at = $user->updated_at;
        $this->user_created_at = $user->created_at;
    }

    static public $expire_window = 86400; // 1day

    /**
     * @throws Exception
     */
    public function fillRandTokenAndExpireAt(): void
    {
        if (is_null($this->token) || strlen($this->token) < 8) {
            $this->token = Random::generateShortToken();
        }
        if (is_null($this->expire_at)) {
            $this->expire_at = time() + static::$expire_window;
        }
    }

    public function isExpired(): bool
    {
        return time() > $this->expire_at;
    }
}
