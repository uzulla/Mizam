<?php
declare(strict_types=1);

namespace Mizam\Repo;

use InvalidArgumentException;

trait ThumbnailGenerateTrait
{
    /**
     * @param string $file_path
     * @param string $to_path
     * @param int $long_px
     */
    public static function createThumbnail(string $file_path, string $to_path, int $long_px = 100): void
    {
        $img = self::imageCreateFromImageFile($file_path);

        $src_w = imagesx($img);
        $src_h = imagesy($img);

        if ($long_px >= $src_w && $long_px >= $src_h) { // リサイズ不要
            imagejpeg($img, $to_path, 80);
            return;
        }

        [$dst_w, $dst_h] = static::convertThumbnailWH($long_px, $src_w, $src_h);

        $thumb = imagecreatetruecolor($dst_w, $dst_h);

        imagecopyresampled(
            $thumb,
            $img,
            0, 0, 0, 0,
            $dst_w, $dst_h,
            $src_w, $src_h
        );

        imagejpeg($thumb, $to_path, 80);

        imagedestroy($thumb);
        imagedestroy($img);
    }

    private static function convertThumbnailWH(int $long_px, int $src_w, int $src_h): array
    {
        $aspect_ratio = $src_w / $src_h;

        if ($aspect_ratio >= 1) {
            $dst_w = $long_px;
            $dst_h = (int)floor($long_px / $aspect_ratio);
        } else {
            $dst_w = (int)floor($long_px * $aspect_ratio);
            $dst_h = $long_px;
        }

        return [$dst_w, $dst_h];
    }

    private static function imageCreateFromImageFile($file_path)
    {
        $info = getimagesize($file_path);
        if ($info[0] === 0 && $info[1] === 0) {
            throw new InvalidArgumentException("this is not image file.");
        }

        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg($file_path);
                break;
            case IMAGETYPE_GIF:
                $img = imagecreatefromgif($file_path);
                break;
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($file_path);
                break;
            default:
                throw new InvalidArgumentException("this is not supported image file.");
        }

        if ($img === false) {
            throw new InvalidArgumentException("this is not supported image file.");
        }

        return $img;
    }
}
