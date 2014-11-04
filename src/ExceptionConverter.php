<?php

namespace Jsor\Stack\Hal;

use Jsor\Stack\Hal\Response\VndErrorResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Converts to a vnd.error response.
 * @see https://github.com/blongden/vnd.error
 */
class ExceptionConverter implements HttpKernelInterface
{
    private $app;
    private $logger;
    private $prettyPrint;
    private $debug;
    private $passThroughCatch;

    public function __construct(HttpKernelInterface $app,
                                LoggerInterface $logger = null,
                                $prettyPrint = false,
                                $debug = false,
                                $passThroughCatch = false)
    {
        $this->app = $app;
        $this->logger = $logger;
        $this->prettyPrint = (bool) $prettyPrint;
        $this->debug = (bool) $debug;
        $this->passThroughCatch = (bool) $passThroughCatch;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        try {
            return $this->app->handle($request, $type, $this->passThroughCatch ? $catch : false);
        } catch (\Exception $exception) {
            if (null !== $this->logger) {
                static::logException($this->logger, $exception);
            }

            if (!$catch) {
                throw $exception;
            }

            return VndErrorResponse::fromException($exception, $this->prettyPrint, $this->debug);
        }
    }

    public static function logException(LoggerInterface $logger, \Exception $exception)
    {
        $message = sprintf(
            'Uncaught PHP Exception %s: "%s" at %s line %s',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        $isCritical = !$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500;
        $context = array('exception' => $exception);

        if ($isCritical) {
            $logger->critical($message, $context);
        } else {
            $logger->error($message, $context);
        }
    }
}
