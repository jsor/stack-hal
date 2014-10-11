<?php

namespace Jsor\Stack\Hal\Configurator;

use Symfony\Component\HttpKernel\HttpKernelInterface;

class NoopConfigurator implements ConfiguratorInterface
{
    public function configureResponseConversion(HttpKernelInterface $app, $prettyPrint = false)
    {
    }
}
