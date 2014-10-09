<?php

namespace Jsor\Stack\Hal\Configurator;

use Symfony\Component\HttpKernel\HttpKernelInterface;

// A HttpKernel (or an application which uses a HttpKernel, like Silex) throws
// an exception if the resolved controller does not return a Response instance.
// The ConfiguratorInterface tries to attach the ResponseConversionListener
// to the HttpKernel's event dispatcher.
interface ConfiguratorInterface
{
    /**
     * @return HttpKernelInterface
     */
    public function configureResponseConversion(HttpKernelInterface $app, $prettyPrint = false);
}
