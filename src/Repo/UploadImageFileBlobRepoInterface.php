<?php
declare(strict_types=1);

namespace Mizam\Repo;

interface UploadImageFileBlobRepoInterface
{
    public static function storeBlob($id, $blob);

    public static function loadBlob($id);

    public static function getBlobStream($id);

    public static function deleteById($file_id);
}
