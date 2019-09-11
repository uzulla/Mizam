<?php
declare(strict_types=1);

namespace Mizam\Repo;

use Exception;
use Mizam\Env;
use Mizam\Model\UploadImageFile;
use Mizam\Model\User;
use PDO;
use RuntimeException;

class UploadImageFileRepo extends DBBase
{
    /**
     * @param int $upload_file_id
     * @param null $pdo
     * @return User|null
     * @throws Exception
     */
    public function getById(int $upload_file_id, $pdo = null): ?UploadImageFile
    {
        if (is_null($pdo)) {
            $pdo = $this->getPdo();
        }

        $stmt = $pdo->prepare("SELECT * FROM upload_files WHERE id=:upload_file_id");
        $stmt->bindValue('upload_file_id', $upload_file_id, PDO::PARAM_INT);
        $stmt->execute();

        /** @var UploadImageFile $uplaod_file */
        $upload_file = $stmt->fetchObject(UploadImageFile::class);

        return $upload_file === false ? null : $upload_file;
    }

    /**
     * @param PDO|null $pdo
     * @return array
     * @throws Exception
     */
    public function getsAll(PDO $pdo = null): array
    {
        if (is_null($pdo)) {
            $pdo = $this->getPdo();
        }

        $stmt = $pdo->prepare("SELECT * FROM upload_files ORDER BY id DESC");
        $stmt->execute();

        $upload_file_list = [];
        while ($upload_file = $stmt->fetchObject(UploadImageFile::class)) {
            $upload_file_list[] = $upload_file;
        }

        return $upload_file_list;
    }

    /**
     * @param UploadImageFile $b
     * @param PDO|null $pdo
     * @return int
     * @throws Exception
     */
    public function save(UploadImageFile $b, PDO $pdo = null): int
    {
        if (is_null($pdo)) {
            $pdo = $this->getPdo();
        }

        $stmt = $pdo->prepare("
INSERT INTO upload_files
(
file_name,
user_id,
size,
created_at
) VALUES (
:file_name,
:user_id,
:size,
:created_at
)
         ");

        $stmt->bindValue('file_name', $b->file_name, PDO::PARAM_STR);
        $stmt->bindValue('user_id', $b->user_id, PDO::PARAM_INT);
        $stmt->bindValue('size', $b->size, PDO::PARAM_INT);
        $stmt->bindValue('created_at', time(), PDO::PARAM_INT);

        $result = $stmt->execute();

        if (!$result) {
            throw new RuntimeException("insert failed.");
        }

        if(Env::getenv("DB_TYPE")=='heroku_pg'){
            $stmt = $pdo->prepare("select lastval() as lastval;");
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $id = (int)$res['lastval'];
        }else {
            $id = (int)$pdo->lastInsertId('id');
        }
        return $id;

    }

    /**
     * @param int $file_id
     * @param PDO|null $pdo
     * @return int
     * @throws Exception
     */
    public function deleteById(int $file_id, PDO $pdo = null): int
    {
        if (is_null($pdo)) {
            $pdo = $this->getPdo();
        }

        $stmt = $pdo->prepare("DELETE FROM upload_files WHERE id=:file_id");
        $stmt->bindValue('file_id', $file_id, PDO::PARAM_INT);
        $stmt->execute();

        $affected_row_num = $stmt->rowCount();

        return $affected_row_num;
    }

}
