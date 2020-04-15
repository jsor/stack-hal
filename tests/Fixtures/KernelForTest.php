<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Fixtures;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class KernelForTest extends Kernel
{
    public function getBundleMap()
    {
        return $this->bundleMap;
    }

    public function registerBundles()
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }

    public function isBooted()
    {
        return $this->booted;
    }
}
