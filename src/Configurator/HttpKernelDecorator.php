<?php

namespace Jsor\Stack\Hal\Configurator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class HttpKernelDecorator implements HttpKernelInterface
{
    private $httpKernel;
    private $configurator;
    private $isConfigured = false;

    public function __construct(HttpKernel $httpKernel, \Closure $configurator)
    {
        $this->httpKernel = $httpKernel;
        $this->configurator = $configurator;
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if (!$this->isConfigured) {
            call_user_func(\Closure::bind($this->configurator, $this->httpKernel, 'Symfony\Component\HttpKernel\HttpKernel'));
            $this->isConfigured = true;
        }

        return $this->httpKernel->handle($request, $type, $catch);
    }
}
