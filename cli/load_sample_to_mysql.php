<?php
require_once(__DIR__ . "/../vendor/autoload.php");

use Dotenv\Dotenv;
use Mizam\Repo\DBBase;
use Mizam\Log;

try {
    $env_type = getenv("ENV") ? getenv("ENV") : "dev";
    error_log("ENV is {$env_type}");

    $dot_env_dir = __DIR__ . "/..";
    if (file_exists($dot_env_dir . "/{$env_type}.env")) {
        Dotenv::create($dot_env_dir, "{$env_type}.env")->load();
        Log::debug("{$env_type}.env loaded:" . print_r(getenv(), true));
    }

    if (getenv("DB_TYPE") != 'mysql') {
        error_log("DB_TYPE is not mysql. please setup env file");
        exit;
    }

    error_log("insert sql" . PHP_EOL);

    $db = DBBase::getPdo();
    $sql = file_get_contents(__DIR__ . "/../db/generate_mysql_ddl.sql");
    $result_row = $db->exec($sql);
    if ($result_row != 0) { // too magical...
        error_log("execute failed.");
    }
    //error_log(  $sql );

    error_log("done");

} catch (Exception $e) {
    echo "unhandled exception:" . $e->getMessage();
    var_dump([
        $e->getFile(),
        $e->getLine(),
        $e->getTrace(),
    ]);
}