<?php
# uploadしてあるzipをサーバーで解凍
# FTPが重いレンサバなどで、`unzip source.zip`が使えない場合などに使う
# ext-zipが必要
ini_set("max_execution_time", 300);
$zip = new \ZipArchive;
if ($zip->open('source.zip') === true) {
    $zip->extractTo(__DIR__ . '/');
    $zip->close();
    echo 'success';
} else {
    echo 'failed';
}
