<?php
declare(strict_types=1);

namespace Mizam\Web;

use Exception;
use Throwable;
use Mizam\Log;
use Mizam\Web\Route\RouteBase;
use Mizam\Web\Route\RouteInterface;
use Dotenv\Dotenv;

class App
{
    public static function boot(): void
    {
        try {
            ini_set('error_reporting', "-1");

            // enable output buffer
            ob_start();

            // load configuration
            static::loadDotEnv();

            // choose session handler
            Session::configureSessionHandler();

            // routing
            $routing_result = RouteBase::routing();

            // execute & send contents
            /** @var RouteInterface $routeInstance */
            $routeInstance = new $routing_result[1];
            $vars = $routing_result[2];
            $routeInstance($vars);

            // flush output buffer
            ob_end_flush();

        } catch (Throwable $e) {
            try {
                Log::dumpOutputBufferToLog();
                Log::error(
                    "uncaught exception {$e->getFile()}:{$e->getLine()} {$e->getMessage()}",
                    ["detail" => print_r($e, true)]
                );

            } catch (Throwable $e) {
                error_log("fatal uncaught exception {$e->getFile()}:{$e->getLine()} {$e->getMessage()}" . print_r($e, true));

            } finally {
                http_response_code(500);
                echo "Internal Server Error";
            }
        }
    }

    /**
     * @throws Exception
     */
    private static function loadDotEnv(): void
    {
        $env_type = getenv("ENV") ?: "dev";

        $dot_env_dir = __DIR__ . "/../..";
        if (file_exists($dot_env_dir . "/{$env_type}.env")) {
            Dotenv::create($dot_env_dir, "{$env_type}.env")->load();
            Log::debug("{$env_type}.env loaded:" . print_r(getenv(), true));
        }
        Log::debug("env is {$env_type}");
    }
}
