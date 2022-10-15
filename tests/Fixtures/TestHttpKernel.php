<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Fixtures;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;

final class TestHttpKernel extends HttpKernel implements ControllerResolverInterface, ArgumentResolverInterface
{
    private $controller;

    public function __construct(EventDispatcherInterface $eventDispatcher, callable $controller = null)
    {
        $this->controller = $controller;

        parent::__construct($eventDispatcher, $this, null, $this);
    }

    public function getController(Request $request): callable|false
    {
        if ($this->controller) {
            return $this->controller;
        }

        return [$this, 'callController'];
    }

    public function getArguments(Request $request, $controller): array
    {
        return [$request];
    }

    public function callController(Request $request)
    {
        return new Response('Request: ' . $request->getRequestUri());
    }
}
