<?php

namespace Jsor\Stack\Hal\Configurator;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Jsor\Stack\Hal\Response\HalResponse;
use Nocarrier\Hal;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LaravelConfigurator implements ConfiguratorInterface
{
    public function configureResponseConversion(HttpKernelInterface $app, $prettyPrint = false)
    {
        $app->extend('router', function (Router $router) use ($prettyPrint) {
            $router->filter('JsorStackHalResponseConverter', function (Route $route, $request) use ($prettyPrint) {
                $hal = $route->run($request);

                if (!$hal instanceof Hal) {
                    return $hal;
                }

                return new HalResponse($hal, 200, [], $prettyPrint);
            });

            $router->when('*', 'JsorStackHalResponseConverter');

            return $router;
        });
    }
}
