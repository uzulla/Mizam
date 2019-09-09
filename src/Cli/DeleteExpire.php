<?php
declare(strict_types=1);

namespace Mizam\Cli;

use Exception;
use Mizam\Service\SignUpTokenService;
use Throwable;
use Mizam\Log;
use Dotenv\Dotenv;

class DeleteExpire
{
    /**
     * @throws Exception
     */
    public static function run(): void
    {
        ini_set('error_reporting', -1);

        Log::debug("start DeleteExpire::run");
        try {
            // load configuration
            static::loadDotEnv();

            $num = SignUpTokenService::deleteExpireRecords();
            Log::debug("SignUpTokenService::deleteExpireRecords is {$num}");
            if ($num > 0) {
                echo "Delete {$num} rows from expired SignUpToken.";
            }

        } catch (Throwable $e) {
            try {
                Log::error(
                    "uncaught exception {$e->getFile()}:{$e->getLine()} {$e->getMessage()}",
                    ["detail" => print_r($e, true)]
                );

            } catch (Throwable $e) {
                error_log("fatal uncaught exception {$e->getFile()}:{$e->getLine()} {$e->getMessage()}" . print_r($e, true));

            } finally {
                die ("Something wrong. check log.");
            }
        }
        Log::debug("finish DeleteExpire::run");
    }

    /**
     * @throws Exception
     */
    private static function loadDotEnv(): void
    {
        $env_type = getenv("ENV") ? getenv("ENV") : "dev";
        Log::debug("env is {$env_type}");

        $dot_env_dir = __DIR__ . "/../..";
        if (file_exists($dot_env_dir . "/{$env_type}.env")) {
            Dotenv::create($dot_env_dir, "{$env_type}.env")->load();
            Log::debug("{$env_type}.env loaded:" . print_r(getenv(), true));
        }
    }
}
