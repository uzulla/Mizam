<?php
declare(strict_types=1);

namespace Mizam\Repo;

use Exception;
use InvalidArgumentException;
use Mizam\Env;
use Mizam\Log;
use RuntimeException;

class UploadImageFileBlobLocalRepo implements UploadImageFileBlobRepoInterface
{
    use ThumbnailGenerateTrait;

    /**
     * @param $id
     * @param $blob
     * @throws Exception
     */
    public static function storeBlob($id, $blob)
    {
        $size = file_put_contents(static::getFileName($id), $blob);
        if ($size === false) {
            throw new RuntimeException("file store failed.");
        }

        self::createThumbnail(static::getFileName($id), static::getThumbnailFileName($id));
    }

    /**
     * @param $id
     * @return false|string
     * @throws Exception
     */
    public static function loadBlob($id)
    {
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
        $store_dir = Env::getenv("LOCAL_BLOB_STORE_DIR");


        if (
            $store_dir === false ||
            !file_exists($store_dir)
        ) {
            throw new InvalidArgumentException("invalid LOCAL_BLOB_STORE_DIR");
        }

        if (is_link($store_dir)) {
            $store_dir = readlink($store_dir);
        }

        if (
            !file_exists($store_dir) ||
            !is_dir($store_dir) ||
            !is_writable($store_dir)
        ) {
            throw new InvalidArgumentException("invalid LOCAL_BLOB_STORE_DIR");
        }

        return "{$store_dir}/{$id}";
    }

    /**
     * @param $id
     * @return string
     * @throws Exception
     */
    private static function getThumbnailFileName($id)
    {
        $store_dir = Env::getenv("LOCAL_THUMBNAIL_STORE_DIR");

        if (
            $store_dir === false ||
            !file_exists($store_dir)
        ) {
            throw new InvalidArgumentException("invalid LOCAL_THUMBNAIL_STORE_DIR");
        }

        if (is_link($store_dir)) {
            $store_dir = readlink($store_dir);
        }

        if (
            !file_exists($store_dir) ||
            !is_dir($store_dir) ||
            !is_writable($store_dir)
        ) {
            throw new InvalidArgumentException("invalid LOCAL_THUMBNAIL_STORE_DIR");
        }

        return "{$store_dir}/{$id}.jpg";
    }

    /**
     * @param int $file_id
     * @throws Exception
     */
    public static function deleteById($file_id)
    {
        if (
        !@unlink(static::getFileName($file_id))
        ) {
            Log::error("delete blob file failed: {$file_id}.  continue...");
        }
        if (
        !@unlink(static::getThumbnailFileName($file_id))
        ) {
            Log::error("delete Thumbnail file failed: {$file_id}. continue...");
        }
    }
}
