<?php
# レンサバなど、htdocsより上位にファイルを設置できない環境用のindex.php
#
# 以下のような配置を想定しています
# /index.php
# /thumbnail_files
# /app/upload_files
# /app/dev.env
# /app/vendor
#

# レンサバだと一旦設定しておくほうが無難です。
ini_set("display_errors", "1");
ini_set('error_reporting', "-1");
# ini_set("error_log", __DIR__."/app/error.log");

require_once(__DIR__ . "/app/vendor/autoload.php");

use Mizam\Web\App;
App::boot();
