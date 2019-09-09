<?php
declare(strict_types=1);

namespace Mizam\Model;

class User
{
    public $id = 0;
    public $login_id;
    public $hashed_password;
    public $nick;
    public $updated_at;
    public $created_at;

    public function __construct()
    {
        if ($this->id == 0) return;

        $this->id = (int)$this->id;
    }

    public function isValidPassword(string $password)
    {
        return password_verify($password, $this->hashed_password);
    }

    public function updatePasswordHash(string $password)
    {
        $this->hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }
}
