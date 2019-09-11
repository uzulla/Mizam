<?php
declare(strict_types=1);

namespace Mizam\Repo;

use Exception;
use InvalidArgumentException;
use Mizam\Env;
use Mizam\Log;
use Mizam\Service\S3ClientService;
use RuntimeException;

class UploadImageFileBlobS3Repo implements UploadImageFileBlobRepoInterface
{
    use ThumbnailGenerateTrait;

    /**
     * @param $id
     * @param $blob
     * @throws Exception
     */
    public static function storeBlob($id, $blob)
    {
        S3ClientService::registerStreamWrapper();

        $size = file_put_contents(static::getFileName($id), $blob);
        if ($size === false) {
            throw new RuntimeException("file store failed.");
        }

        self::createThumbnail(static::getFileName($id), static::getThumbnailFileName($id));

        $s3_client = S3ClientService::get();

        $result = $s3_client->putObjectAcl([
                'ACL' => 'public-read',
                'Bucket' => self::getBucketName(),
                'Key' => "thumbnail_images/{$id}.jpg",
            ]);

        Log::debug("S3client putObjectAcl result", $result->toArray());
    }

    /**
     * @param $id
     * @return false|string
     * @throws Exception
     */
    public static function loadBlob($id)
    {
        S3ClientService::registerStreamWrapper();

        $raw = file_get_contents(static::getFileName($id));
        if ($raw === false) {
            throw new RuntimeException("file read failed.");
        }
        return $raw;
    }

    /**
     * @param $id
     * @return bool|resource
     * @throws Exception
     */
    public static function getBlobStream($id)
    {
        S3ClientService::registerStreamWrapper();

        $fh = fopen(static::getFileName($id), 'r');
        if ($fh === false) {
            throw new RuntimeException("file open failed.");
        }
        return $fh;
    }


    /**
     * @param $id
     * @return string
     * @throws Exception
     */
    private static function getFileName($id)
    {
        $bucket = self::getBucketName();

        return "s3://{$bucket}/original_files/{$id}";
    }

    /**
     * @param $id
     * @return string
     * @throws Exception
     */
    private static function getThumbnailFileName($id)
    {
        $bucket = self::getBucketName();
        return "s3://{$bucket}/thumbnail_images/{$id}.jpg";
    }

    /**
     * @param int $file_id
     * @throws Exception
     */
    public static function deleteById($file_id)
    {
        // TODO
    }

    private static function getBucketName():string
    {
        $bucket = Env::getenv("S3_BUCKET_NAME");
        if ($bucket === false) {
            throw new InvalidArgumentException("ENV:S3_BUCKET_NAME is undefined");
        }
        return $bucket;
    }
}
