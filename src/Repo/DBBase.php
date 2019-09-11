<?php
declare(strict_types=1);

namespace Mizam\Repo;

use Exception;
use InvalidArgumentException;
use Mizam\Env;
use Mizam\Log;
use PDO;

class DBBase
{
    static $db_type;

    /** @var null|PDO */
    static $pdo = null;

    public function __construct(PDO $pdo = null)
    {
        if (!is_null($pdo)) {
            static::$pdo = $pdo;
        }
    }

    /**
     * @return PDO
     * @throws Exception
     */
    public static function getPdo(): PDO
    {
        if (is_null(static::$pdo)) {
            static::$pdo = static::getNewPdo();
        }
        return static::$pdo;
    }

    /**
     * @return PDO
     * @throws Exception
     */
    public static function getNewPdo(): PDO
    {
        static::$db_type = Env::getenv("DB_TYPE");
        if (static::$db_type === false) {
            throw new InvalidArgumentException("undefined DB_TYPE in env.");
        }

        if (static::$db_type == 'sqlite') {
            $dsn = Env::getenv("DB_DSN");
            Log::debug("dsn: $dsn");

            if ($dsn === false) {
                throw new InvalidArgumentException("undefined DB_DSN.");
            }

            $pdo = new PDO($dsn);

        } else if (static::$db_type === 'mysql') {
            $dsn = Env::getenv("DB_DSN");
            Log::debug("dsn: $dsn");
            $db_user_name = Env::getenv("DB_USER_NAME");
            $db_user_pass = Env::getenv("DB_USER_PASS");

            if ($dsn === false) {
                throw new InvalidArgumentException("undefined DB_DSN.");
            }

            $pdo = new PDO($dsn, $db_user_name, $db_user_pass);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        } else if (static::$db_type === 'heroku_pg') {

            $db = parse_url(Env::getenv("DATABASE_URL"));
            Log::debug("parsed_db: ", $db);
            $pdo = new PDO("pgsql:" . sprintf(
                    "host=%s;port=%s;user=%s;password=%s;dbname=%s",
                    $db["host"],
                    $db["port"],
                    $db["user"],
                    $db["pass"],
                    ltrim($db["path"], "/")
                ));
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        } else {
            throw new InvalidArgumentException("invalid db_type in env");

        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    public static function getRandFuncName(): string
    {
        if (static::$db_type === 'sqlite') {
            return "RANDOM()";
        } else if (static::$db_type === 'mysql') {
            return "RAND()";
        } else {
            throw new InvalidArgumentException("invalid db_type");
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function beginTransaction(): bool
    {
        return (static::getPdo())->beginTransaction();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function commitTransaction(): bool
    {
        return (static::getPdo())->commit();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function rollbackTransaction(): bool
    {
        return (static::getPdo())->rollBack();
    }
}