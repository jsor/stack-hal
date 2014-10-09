<?php

namespace Jsor\Stack\Hal\Configurator;

use Jsor\Stack\Hal\EventListener\ResponseConversionListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class HttpKernelConfigurator implements ConfiguratorInterface
{
    public function configureResponseConversion(HttpKernelInterface $app, $prettyPrint = false)
    {
        return new HttpKernelDecorator($app, function () use ($prettyPrint) {
            $this->dispatcher->addSubscriber(new ResponseConversionListener($prettyPrint));
        });
    }
}
