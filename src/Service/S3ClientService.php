<?php
declare(strict_types=1);

namespace Mizam\Service;

use Aws\S3\S3Client;
use InvalidArgumentException;

class S3ClientService
{
    static public function get(): S3Client
    {
        static $s3;
        if (!is_null($s3)) {
            return $s3;
        }

        // s3 の認証情報は、ENV: AWS_ACCESS_KEY_ID および AWS_SECRET_ACCESS_KEYから発見される
        if (getenv("AWS_ACCESS_KEY_ID") === false || getenv("AWS_SECRET_ACCESS_KEY") === false) {
            throw new InvalidArgumentException("AWS_ACCESS_KEY_ID or AWS_SECRET_ACCESS_KEY is undefined");
        }

        $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'ap-northeast-1' // ハードコード
        ]);

        return $s3;
    }

    static public function registerStreamWrapper(): void
    {
        static $flag;

        if (is_null($flag)) {
            static::get()->registerStreamWrapper();
            $flag = true;
        }
    }
}