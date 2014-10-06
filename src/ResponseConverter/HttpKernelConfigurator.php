<?php

namespace Jsor\Stack\Hal\ResponseConverter;

// A HttpKernel (or an application which uses a HttpKernel, like Silex) throws
// an exception if the resolved controller does not return a Response instance.
// The HttpKernelConfigurator tries to attach the EventSubscriber to the
// HttpKernel's event dispatcher.
interface HttpKernelConfigurator
{
    public function configureHttpKernel($app, $prettyPrint = false);
}
