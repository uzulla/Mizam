<?php
declare(strict_types=1);

namespace Mizam\Service;

use Exception;
use InvalidArgumentException;
use Mizam\Model\UploadImageFile as UploadFileAlias;
use Mizam\Model\UploadImageFile;
use Mizam\Model\User;
use Mizam\Repo\UploadImageFileBlobRepoInterface;
use Mizam\Repo\UploadImageFileRepo;

class UploadImageFileService
{
    /**
     * @param User $user
     * @param string $upload_file_path
     * @param string $file_name
     * @return UploadFileAlias
     * @throws Exception
     */
    public static function storeFile(User $user, string $upload_file_path, string $file_name)
    {
        $pdo = UploadImageFileRepo::getNewPdo();
        try {
            $pdo->beginTransaction();

            $b = new UploadFileAlias();
            $b->user_id = $user->id;
            $b->file_name = $file_name;
            $b->size = filesize($upload_file_path);
            $b->created_at = time();

            $upload_image_file_repo = new UploadImageFileRepo();
            $upload_file_id = $upload_image_file_repo->save($b, $pdo);

            $blob_repo = static::getBlobRepo();
            $blob_repo->storeBlob($upload_file_id, file_get_contents($upload_file_path));

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

        return $b;
    }

    private static function getBlobRepo(): UploadImageFileBlobRepoInterface
    {
        $blob_store_class_name = getenv("BLOB_STORE_CLASS");
        if ($blob_store_class_name === false) {
            throw new InvalidArgumentException("undefined BLOB_STORE_CLASS");
        }
        /** @var UploadImageFileBlobRepoInterface $blob_class */
        $blob_class = new $blob_store_class_name;
        return $blob_class;
    }

    /**
     * @return UploadImageFile[]
     * @throws Exception
     */
    public static function getAll(): array
    {
        $repo = new UploadImageFileRepo();
        $list = $repo->getsAll();
        return $list;
    }

    public static function getFileHandler($id)
    {
        /** @var UploadImageFileBlobRepoInterface $repo */
        $repo = static::getBlobRepo();
        return $repo->getBlobStream($id);
    }

    /**
     * @param $id
     * @return UploadImageFile|null
     * @throws Exception
     */
    public static function getById($id): ?UploadImageFile
    {
        $repo = new UploadImageFileRepo();
        return $repo->getById($id);
    }

    /**
     * @param int $file_id
     * @throws Exception
     */
    public static function deleteById(int $file_id)
    {
        $pdo = UploadImageFileRepo::getNewPdo();
        try {
            $pdo->beginTransaction();
            $repo = new UploadImageFileRepo();
            $row = $repo->deleteById($file_id, $pdo);

            if ($row !== 1) {
                throw new InvalidArgumentException("delete row failed");
            }

            $blob_repo = static::getBlobRepo();
            $blob_repo->deleteById($file_id);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
