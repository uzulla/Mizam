<?php
declare(strict_types=1);

namespace Mizam\Web;

use Mizam\Env;
use Throwable;
use Mizam\Log;
use Mizam\Web\Route\RouteBase;
use Mizam\Web\Route\RouteInterface;

class App
{
    public static function boot(): void
    {
        try {
            ini_set('error_reporting', "-1");

            // enable output buffer
            ob_start();

            // load configuration
            Env::loadDotEnv();

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
}
