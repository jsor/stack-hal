<?php

namespace Jsor\Stack\Hal\Configurator;

use Jsor\Stack\Hal\EventListener\ResponseConversionListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Silex1Configurator implements ConfiguratorInterface
{
    public function configureResponseConversion(HttpKernelInterface $app, $prettyPrint = false)
    {
        $app->share($app->extend('dispatcher', function ($dispatcher) use ($prettyPrint) {
            $dispatcher->addSubscriber(new ResponseConversionListener($prettyPrint));

            return $dispatcher;
        }));
    }
}
