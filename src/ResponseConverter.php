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
    private $configurator;
    private $isConfigured = false;

    public function __construct(HttpKernelInterface $app,
                                $prettyPrint = false,
                                ConfiguratorInterface $configurator = null)
    {
        $this->app = $app;
        $this->prettyPrint = (bool) $prettyPrint;
        $this->configurator = $configurator;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $this->configure();

        $hal = $this->app->handle($request, $type, $catch);

        if (!$hal instanceof Hal) {
            return $hal;
        }

        return new HalResponse($hal, 200, [], $this->prettyPrint);
    }

    protected function configure()
    {
        if ($this->isConfigured) {
            return;
        }

        if (!$this->configurator) {
            $guesser = new ConfiguratorGuesser();
            $this->configurator = $guesser->guess($this->app);
        }

        $this->configurator->configureResponseConversion($this->app, $this->prettyPrint);
        $this->isConfigured = true;
    }
}
