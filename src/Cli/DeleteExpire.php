<?php
declare(strict_types=1);

namespace Mizam\Cli;

use Exception;
use Mizam\Env;
use Mizam\Service\SignUpTokenService;
use Throwable;
use Mizam\Log;

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
            Env::loadDotEnv();

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
}
