<?php

namespace Jsor\Stack\Hal\Configurator;

use Jsor\Stack\Hal\EventListener\ResponseConversionListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class KernelConfigurator implements ConfiguratorInterface
{
    public function configureResponseConversion(HttpKernelInterface $app, $prettyPrint = false)
    {
        call_user_func(\Closure::bind(function () use ($prettyPrint) {
            $this->dispatcher->addSubscriber(new ResponseConversionListener($prettyPrint));
        }, $app->getContainer()->get('http_kernel'), 'Symfony\Component\HttpKernel\HttpKernel'));
    }
}
