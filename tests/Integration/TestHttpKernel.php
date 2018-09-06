<?php

namespace Jsor\Stack\Hal\Integration;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Tests\TestHttpKernel as SymfonyTestHttpKernel;

final class TestHttpKernel extends SymfonyTestHttpKernel
{
    private $controller;

    public function __construct(EventDispatcherInterface $eventDispatcher, callable $controller = null)
    {
        $this->controller = $controller;

        HttpKernel::__construct($eventDispatcher, $this);
    }

    public function getController(Request $request)
    {
        if ($this->controller) {
            return $this->controller;
        }

        return parent::getController($request);
    }
}
