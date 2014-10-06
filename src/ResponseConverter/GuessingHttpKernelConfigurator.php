<?php

namespace Jsor\Stack\Hal\ResponseConverter;

use Symfony\Component\HttpKernel\HttpKernel;

class GuessingHttpKernelConfigurator extends HttpKernel implements HttpKernelConfigurator
{
    public function __construct()
    {
    }

    public function configureHttpKernel($app, $prettyPrint = false)
    {
        if ($app instanceof \Silex\Application) {
            if (method_exists($app, 'share')) { // Silex 1.x
                $app['dispatcher'] = $app->share($app->extend('dispatcher', function ($dispatcher) use ($prettyPrint) {
                    $dispatcher->addSubscriber(new EventSubscriber($prettyPrint));

                    return $dispatcher;
                }));
            } else { // Silex 2.x
                $app['dispatcher'] = $app->extend('dispatcher', function ($dispatcher) use ($prettyPrint) {
                    $dispatcher->addSubscriber(new EventSubscriber($prettyPrint));

                    return $dispatcher;
                });
            }

            return;
        }

        if ($app instanceof HttpKernel) {
            $app->dispatcher->addSubscriber(new EventSubscriber($prettyPrint));
        }
    }
}
