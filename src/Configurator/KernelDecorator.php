<?php

namespace Jsor\Stack\Hal\Configurator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

class KernelDecorator implements HttpKernelInterface
{
    private $kernel;
    private $httpKernelConfigurator;
    private $decorator;

    public function __construct(Kernel $kernel, \Closure $httpKernelConfigurator)
    {
        $this->kernel = $kernel;
        $this->httpKernelConfigurator = $httpKernelConfigurator;
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if (!$this->decorator) {
            $this->kernel->boot();
            $this->decorator = new HttpKernelDecorator(
                $this->kernel->getContainer()->get('http_kernel'),
                $this->httpKernelConfigurator
            );
        }

        return $this->decorator->handle($request, $type, $catch);
    }
}
