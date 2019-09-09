<?php
declare(strict_types=1);

namespace Mizam\Web\Traits;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

trait TemplateEngineTrait
{
    static $twigInstance;

    static public function getTwig(): Environment
    {
        if (is_null(static::$twigInstance)) {
            $loader = new FilesystemLoader(__DIR__ . '/../../../templates/web');
            static::$twigInstance = new Environment($loader, []);
        }
        return static::$twigInstance;
    }

    /**
     * Renders a template.
     *
     * @param string $templateName
     * @param array $val
     * @return string The rendered template
     *
     * @throws LoaderError When the template cannot be found
     * @throws RuntimeError When an error occurred during rendering
     * @throws SyntaxError When an error occurred during compilation
     */
    static public function render(string $templateName, array $val = []): string
    {
        return static::getTwig()->render($templateName, $val);
    }
}
