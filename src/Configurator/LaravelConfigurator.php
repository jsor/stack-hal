<?php

namespace Jsor\Stack\Hal\Configurator;

use Illuminate\Routing\Router;
use Jsor\Stack\Hal\ResponseConverter;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LaravelConfigurator implements ConfiguratorInterface
{
    public function configureResponseConversion(HttpKernelInterface $app, $prettyPrint = false)
    {
        $app->extend('router', function (Router $router) use ($prettyPrint) {
            $router->filter('JsorStackHalResponseConverter', function ($route, $request) use ($prettyPrint) {
                return ResponseConverter::convert(
                    $route->run($request),
                    $request,
                    $prettyPrint
                );
            });

            $router->when('*', 'JsorStackHalResponseConverter');

            return $router;
        });

        return $app;
    }
}
