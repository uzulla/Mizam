<?php
declare(strict_types=1);

namespace Mizam\Web\Route;

use Exception;
use Mizam\Log;
use FastRoute\Dispatcher;
use FastRoute;
use FastRoute\RouteCollector;

class RouteBase
{
    public static function setupRouteDispatcher(): Dispatcher
    {
        $route_collector = function (RouteCollector $router) {
            $router->addRoute('GET', '/', SiteTop::class);
            $router->addRoute('GET', '/sign/in', SignInForm::class);
            $router->addRoute('POST', '/sign/in', SignIn::class);
            $router->addRoute('POST', '/sign/out', SignOut::class);
            $router->addRoute('POST', '/upload', FileUpload::class);
            $router->addRoute('GET', '/download/{id:\d+}', FileDownload::class);
            $router->addRoute('POST', '/delete/{id:\d+}', FileDelete::class);
            $router->addRoute('GET', '/sign/up', SignUpForm::class);
            $router->addRoute('POST', '/sign/up', SignUp::class);
            $router->addRoute('GET', '/sign/up/verify/{token}', SignUpVerify::class);
        };
        return FastRoute\simpleDispatcher($route_collector);
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function routing(): array
    {
        $dispatcher = static::setupRouteDispatcher();

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        Log::debug("request info", ['httpMethod' => $httpMethod, 'raw_uri' => $uri]);

        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        // write access log
        Log::event("access to {$uri}");

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        // If did not found any defined route. (don't care method not allowed)
        if ($routeInfo[0] !== FastRoute\Dispatcher::FOUND) {
            Log::error("Notfound route", ['routing_result' => $routeInfo]);

            $routeInfo[0] = FastRoute\Dispatcher::FOUND;
            $routeInfo[1] = NotfoundRoute::class;
            $routeInfo[2] = [];
        }

        return $routeInfo;
    }
}
