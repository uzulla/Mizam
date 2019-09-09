<?php
declare(strict_types=1);

namespace Mizam\Web\Route;

use Exception;
use Mizam\Service\UploadImageFileService;
use Mizam\Web\Traits\EmitterTrait;
use Mizam\Web\Traits\TemplateEngineTrait;
use Mizam\Web\Traits\UserSessionTrait;

class SiteTop implements RouteInterface
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
        $params = [];
        $params = array_merge(
            $params,
            [
                'user_session' => static::getUserSession(),
                'image_list' => UploadImageFileService::getAll(),
            ]
        );

        static::send(self::render('index.twig', $params));
    }
}
