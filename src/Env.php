<?php
declare(strict_types=1);

namespace Mizam;

use Dotenv\Dotenv;
use Exception;

class Env
{
    public static function getenv(string $name = null)
    {
        static $is_getenv_enabled = null;

        if (is_null($is_getenv_enabled)) {
            if (
                !function_exists("putenv") ||
                !function_exists("getenv")
            ) {
                // oh-no.
                $is_getenv_enabled = false;
            } else {
                $is_getenv_enabled = true;
            }
        }

        if ($is_getenv_enabled) {
            if (is_null($name)) {
                return getenv();
            } else {
                return getenv($name);
            }
        } else {
            if (is_null($name)) {
                return $_ENV;
            } else {
                return isset($_ENV[$name]) ? $_ENV[$name] : false;
            }
        }
    }

    static $dot_env_dir = __DIR__ . "/..";

    /**
     * @throws Exception
     */
    public static function loadDotEnv(): void
    {
        $env_type = Env::getenv("ENV") ?: "dev";

        $dot_env_dir = static::$dot_env_dir;
        $dot_env_file_name = "{$env_type}.env";
        $dot_env_file_path = "{$dot_env_dir}/{$dot_env_file_name}";

        if (file_exists($dot_env_file_path)) {
            Dotenv::create($dot_env_dir, $dot_env_file_name)->load();
            Log::debug("{$dot_env_file_path} loaded:" . print_r(Env::getenv(), true));
        }else{
            Log::debug("{$dot_env_file_path} env not loaded:" . print_r(Env::getenv(), true));
        }

        if(Env::getenv("SITE_URL")===false){
            echo "Error, env isn't loaded";
            error_log("Error, env isn't loaded");
            exit;
        }

        Log::debug("env is {$env_type}");
    }

}