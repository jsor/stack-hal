<?php

namespace Jsor\Stack\Hal;

use Jsor\Stack\Hal\Configurator\ConfiguratorGuesser;
use Jsor\Stack\Hal\Configurator\ConfiguratorInterface;
use Jsor\Stack\Hal\Response\HalResponse;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponseConverter implements HttpKernelInterface
{
    private $app;
    private $prettyPrint;

    public function __construct(HttpKernelInterface $app,
                                $prettyPrint = false,
                                ConfiguratorInterface $configurator = null)
    {
        $this->prettyPrint = (bool) $prettyPrint;

        if (!$configurator) {
            $guesser = new ConfiguratorGuesser();
            $configurator = $guesser->guess($app);
        }

        $this->app = $configurator->configureResponseConversion($app, $this->prettyPrint);
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $hal = $this->app->handle($request, $type, $catch);

        if (!$hal instanceof Hal) {
            return $hal;
        }

        return HalResponse::create($hal, 200, [], $this->prettyPrint);
    }
}
