<?php
declare(strict_types=1);

namespace Mizam\Model;

use Mizam\Env;

class UploadImageFile
{
    public $id = 0;
    public $user_id = 0;
    public $file_name = "";
    public $size = 0;
    public $created_at = 0;

    public function __construct()
    {
        if ($this->id == 0) return;

        $this->id = (int)$this->id;
        $this->user_id = (int)$this->user_id;
        $this->size = (int)$this->size;
        $this->created_at = (int)$this->created_at;
    }

    public function getDownloadUrl()
    {
        return "/download/{$this->id}";
    }

    public function getFileName()
    {
        return $this->file_name;
    }

    public function getThumbnailUrl()
    {
        $base_url = Env::getenv("THUMBNAIL_STORE_BASE_URL");
        return "{$base_url}/{$this->id}.jpg";
    }
}
