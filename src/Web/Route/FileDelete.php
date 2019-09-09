<?php
declare(strict_types=1);

namespace Mizam\Web\Route;

use Exception;
use Mizam\Log;
use Mizam\Service\UploadImageFileService;
use Mizam\Web\Traits\EmitterTrait;
use Mizam\Web\Traits\TemplateEngineTrait;
use Mizam\Web\Traits\UserSessionTrait;

class FileDelete implements RouteInterface
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
        if (!static::validateCsrfToken()) {
            (new BadCsrfTokenRequest())([]);
            return;
        }


        $file_id = (int)$vars['id'];

        $user_session = static::getUserSession();
        $params = ['user_session' => $user_session];

        if (is_null($user_session)) {
            $params = array_merge($params, ['errors' => ['session_error' => 'session required.']]);
            static::send(self::render('index', $params));
            return;
        }

        $file = UploadImageFileService::getById($file_id);
        if (is_null($file)) {
            (new NotfoundRoute())([]);
            return;
        }

        if ($file->user_id !== $user_session->user_id) {
            (new Forbidden())([]);
            return;
        }

        UploadImageFileService::deleteById($file_id);

        Log::event("delete file: :{$vars['id']}");

        static::redirect("/");
    }
}
