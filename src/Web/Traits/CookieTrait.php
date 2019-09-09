<?php
declare(strict_types=1);

namespace Mizam\Web\Traits;

use Exception;
use InvalidArgumentException;
use Mizam\Log;

trait CookieTrait
{
    /** @var string */
    public $name = null;
    /** @var string */
    public $value = null;
    /** @var int */
    public $expire = null;
    /** @var string */
    public $path = null;
    /** @var string */
    public $domain = null;
    /** @var bool */
    public $httponly = null;
    /** @var bool */
    public $secure = null;

    /**
     * @throws Exception
     */
    public function send(): void
    {
        if (is_null($this->name) || is_null($this->value)) {
            throw new InvalidArgumentException("key and val are require");
        }

        Log::debug("send cookie", [json_decode(json_encode($this))]);

        setcookie(
            $this->name,
            $this->value,
            $this->expire,
            $this->path,
            $this->domain,
            $this->secure,
            $this->httponly
        );
    }
}
