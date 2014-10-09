<?php

namespace Jsor\Stack\Hal\Configurator;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

class ConfiguratorGuesser
{
    public function guess(HttpKernelInterface $app)
    {
        if ($app instanceof \Silex\Application) {
            if (version_compare(\Silex\Application::VERSION, '2', '<')) {
                return new Silex1Configurator();
            }

            return new Silex2Configurator();
        }

        if ($app instanceof \Illuminate\Foundation\Application) {
            return new LaravelConfigurator();
        }

        if ($app instanceof Kernel) {
            return new KernelConfigurator();
        }

        if ($app instanceof HttpKernel) {
            return new HttpKernelConfigurator();
        }

        return new NoopConfigurator();
    }
}
