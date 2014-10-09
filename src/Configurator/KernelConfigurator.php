<?php

namespace Jsor\Stack\Hal\Configurator;

use Jsor\Stack\Hal\EventListener\ResponseConversionListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class KernelConfigurator implements ConfiguratorInterface
{
    public function configureResponseConversion(HttpKernelInterface $app, $prettyPrint = false)
    {
        return new KernelDecorator($app, function () use ($prettyPrint) {
            $this->dispatcher->addSubscriber(new ResponseConversionListener($prettyPrint));
        });
    }
}
