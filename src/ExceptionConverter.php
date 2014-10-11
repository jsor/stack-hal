<?php

namespace Jsor\Stack\Hal;

use Jsor\Stack\Hal\Response\VndErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Converts to a vnd.error response.
 * @see https://github.com/blongden/vnd.error
 */
class ExceptionConverter implements HttpKernelInterface
{
    private $app;
    private $prettyPrint;
    private $passThroughCatch;

    public function __construct(HttpKernelInterface $app,
                                $prettyPrint = false,
                                $passThroughCatch = false)
    {
        $this->app = $app;
        $this->prettyPrint = (bool) $prettyPrint;
        $this->passThroughCatch = (bool) $passThroughCatch;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        try {
            return $this->app->handle($request, $type, $this->passThroughCatch ? $catch : false);
        } catch (\Exception $exception) {
            if (!$catch) {
                throw $exception;
            }

            return VndErrorResponse::fromException($exception, $this->prettyPrint);
        }
    }
}
