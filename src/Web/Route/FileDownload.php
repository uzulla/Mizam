<?php
declare(strict_types=1);

namespace Mizam\Web\Route;

use Exception;
use Mizam\Log;
use Mizam\Service\UploadImageFileService;
use Mizam\Web\Traits\EmitterTrait;
use Mizam\Web\Traits\TemplateEngineTrait;
use Mizam\Web\Traits\UserSessionTrait;

class FileDownload implements RouteInterface
{
    use TemplateEngineTrait;
    use EmitterTrait;
    use UserSessionTrait;

    /**
     * @param array $vars
     * @throws Exception
     */
    public function __invoke(array $vars): void
    {
        Log::debug("vars", $vars);

        $file_id = (int)$vars['id'];

        $user_session = static::getUserSession();
        $params = ['user_session' => $user_session];

        if (is_null($user_session)) {
            $params = array_merge($params, ['errors' => ['session_error' => 'session required.']]);
            static::send(self::render('index.twig', $params));
            return;
        }

        $file = UploadImageFileService::getById($file_id);
        if (is_null($file)) {
            (new NotfoundRoute())([]);
            return;
        }

        $fh = UploadImageFileService::getFileHandler($file_id);

        static::sendStream(
            $fh,
            [
                'Content-type' => "application/octet-stream",
                "Content-Disposition" => "attachment; filename=\"{$file->getFileName()}\"",
                "Expires" => "0",
                "Cache-Control" => "no-store",
            ]
        );
    }
}
