<?php
declare(strict_types=1);

namespace Mizam\Web\Route;

use Exception;
use Mizam\Service\UploadImageFileService;
use Mizam\Web\Traits\EmitterTrait;
use Mizam\Web\Traits\TemplateEngineTrait;
use Mizam\Web\Traits\UserSessionTrait;

class FileUpload implements RouteInterface
{
    use TemplateEngineTrait;
    use EmitterTrait;
    use UserSessionTrait;

    static $allow_suffix_list = ['jpg', 'jpeg', 'gif', 'png'];
    static $suffix_mime_list = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png'
    ];

    /**
     * @param array $vars
     * @throws Exception
     */
    public function __invoke(array $vars): void
    {
        if (!static::validateCsrfToken()) {
            (new BadCsrfTokenRequest())([]);
            return;
        }

        $user_session = static::getUserSession();
        $user = $user_session->getUser();
        $params = ['user_session' => $user_session];


        if (is_null($user_session)) {
            $params = array_merge($params, ['errors' => ['session_error' => 'session required.']]);
            static::send(self::render('index', $params));
            return;
        }

        // validation
        $error_list = [];

        if (
            !isset($_FILES['file']) ||
            !isset($_FILES['file']['error']) ||
            $_FILES['file']['error'] !== UPLOAD_ERR_OK
        ) {
            $error_list['file'] = "must be file size <= 10M byte";

        } elseif (
            $_FILES['file']['size'] > 10 * 1024 * 1024 // 10M byte 以上を拒否
        ) {
            $error_list['file'] = "must be file size <= 10M byte {$_FILES['file']['size']}";

        } else {
            $suffix = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            $match_suffix = array_filter(static::$allow_suffix_list, function ($allow_suffix) use ($suffix) {
                return preg_match("/\A{$allow_suffix}\z/i", $suffix);
            });
            if (count($match_suffix) === 0) {
                $error_list['file'] = "not allowed file type. allowed file type list: " . implode(",", static::$allow_suffix_list);
            }
        }

        if (count($error_list) > 0) {
            $params = array_merge($params, ['errors' => $error_list]);

            static::send(self::render('index.twig', $params));
            return;
        }

        // store upload image
        UploadImageFileService::storeFile($user, $_FILES['file']['tmp_name'], $_FILES['file']['name']);

        static::redirect('/');
    }

    public static function getMimeFromSuffix(string $suffix): string
    {
        return static::$suffix_mime_list[$suffix] ?? 'application/octet-stream';
    }
}
