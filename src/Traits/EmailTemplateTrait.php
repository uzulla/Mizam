<?php
declare(strict_types=1);

namespace Mizam\Traits;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

trait EmailTemplateTrait
{
    static $emailTwigInstance;

    static public function getMailTwig(): Environment
    {
        if (is_null(static::$emailTwigInstance)) {
            $loader = new FilesystemLoader(__DIR__ . '/../../templates/mail');
            static::$emailTwigInstance = new Environment($loader, []);
        }
        return static::$emailTwigInstance;
    }

    /**
     * @param string $templateName
     * @param array $val
     * @return array
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    static public function renderMail(string $templateName, array $val = []): array
    {
        $str = static::getMailTwig()->render($templateName, $val);
        return explode("\n", $str, 2);
    }
}
